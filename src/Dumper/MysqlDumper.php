<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper;

use Druidfi\Mysqldump\Mysqldump;
use Smile\GdprDump\Config\DumperConfig;
use Smile\GdprDump\Config\Loader\ConfigLoader;
use Smile\GdprDump\Database\ConnectionProvider;
use Smile\GdprDump\Database\DatabaseFactory;
use Smile\GdprDump\Dumper\Builder\MysqldumpSettingsBuilder;
use Smile\GdprDump\Dumper\Config\ConfigProcessor;
use Smile\GdprDump\Dumper\Event\DatabaseConnectedEvent;
use Smile\GdprDump\Dumper\Event\DumpEvent;
use Smile\GdprDump\Dumper\Event\DumpFinishedEvent;
use Smile\GdprDump\Dumper\Event\DumpTerminated;
use Smile\GdprDump\Dumper\Event\TerminateEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class MysqlDumper implements Dumper
{
    public function __construct(
        private DatabaseFactory $databaseFactory,
        private MysqldumpSettingsBuilder $mysqldumpSettingsBuilder,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function dump2(ConfigLoader $configLoader, bool $dryRun = false): void
    {
        try {
            $config = $configLoader->load();
            $params = $config->database ?? [];
            $params = (array) $params;
            $params['dbname'] = $params['name'] ?? throw new \Exception('Need to specify database name!');

            $database = new \Smile\GdprDump\Database\Database(new \Smile\GdprDump\Database\ParameterBag($params));
            $database->connect();
            $this->eventDispatcher->dispatch(new DatabaseConnectedEvent($database));

            $start = microtime(true);
            $config = $configLoader->load();
            var_dump($config->tables ?? throw new \Exception('Must specify some tables for this test'));
            var_dump(microtime(true) - $start);
            $database->close();
        } finally {
            $this->eventDispatcher->dispatch(new DumpTerminated());
        }
    }

    public function dump(DumperConfig $config, bool $dryRun = false): void
    {
        try {
            // Initialize the database connection
            $database = $this->databaseFactory->create($config);
            $database->connect();
            $this->eventDispatcher->dispatch(new DatabaseConnectedEvent($database));

            // Process tables declared in the configuration (remove undefined tables, resolve patterns such as "log_*")
            $processor = new ConfigProcessor($database->getMetadata());
            $processor->process($config);

            // TODO replace old config object in below lines and dependencies

            // Create the Mysqldump object (mysqldump-php library)
            $dumpContext = new DumpContext();
            $dumper = $this->createMysqldump($database, $config, $dumpContext);

            $this->eventDispatcher->dispatch(new DumpEvent($dumper, $database, $config, $dumpContext));

            // Close the Doctrine connection before proceeding to the dump creation (mysqldump-php uses its own connection)
            $database->close();

            if (!$dryRun) {
                // Create the dump
                $dumper->start($config->getDumpOutput());
            }

            $this->eventDispatcher->dispatch(new DumpFinishedEvent($config));
        } finally {
            $this->eventDispatcher->dispatch(new DumpTerminated());
        }
    }

    /**
     * Create the Mysqldump object.
     */
    private function createMysqldump(
        ConnectionProvider $database,
        DumperConfig $config,
        DumpContext $dumpContext,
    ): Mysqldump {
        $dumpSettings = $this->mysqldumpSettingsBuilder->build($config);

        // Set SQL variables
        $connection = $database->getConnection();
        foreach ($config->getVarQueries() as $varName => $query) {
            $value = $connection->fetchOne($query);
            $dumpContext->variables[$varName] = $value;
            $dumpSettings['init_commands'][] = 'SET @' . $varName . ' = ' . $connection->quote($value);
        }

        return new Mysqldump(
            $database->getDriver()->getDsn(),
            $database->getConnectionParams()->get('user'),
            $database->getConnectionParams()->get('password'),
            $dumpSettings,
            $database->getConnectionParams()->get('driverOptions', [])
        );
    }
}
