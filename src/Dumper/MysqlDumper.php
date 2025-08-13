<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper;

use Druidfi\Mysqldump\Mysqldump;
use Smile\GdprDump\Configuration\Configuration;
use Smile\GdprDump\Database\Database;
use Smile\GdprDump\Database\DatabaseFactory;
use Smile\GdprDump\Dumper\Builder\MysqldumpSettingsBuilder;
use Smile\GdprDump\Dumper\Config\ConfigProcessor;
use Smile\GdprDump\Dumper\Event\DumpEvent;
use Smile\GdprDump\Dumper\Event\DumpFinishedEvent;
use Smile\GdprDump\Dumper\Exception\DumpException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Throwable;

final class MysqlDumper implements Dumper
{
    public function __construct(
        private DatabaseFactory $databaseFactory,
        private MysqldumpSettingsBuilder $mysqldumpSettingsBuilder,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function dump(Configuration $configuration, bool $dryRun = false): void
    {
        try {
            $configuration = clone $configuration; // object will be modified by ConfigProcessor

            // Initialize the database connection
            $database = $this->databaseFactory->create($configuration->getConnectionParams());
            $database->connect();

            // Process tables declared in the configuration (remove undefined tables, resolve patterns such as "log_*")
            $processor = new ConfigProcessor($database->getMetadata());
            $processor->process($configuration);

            // Create the Mysqldump object (mysqldump-php library)
            $dumpContext = new DumpContext();
            $dumper = $this->createMysqldump($database, $configuration, $dumpContext);

            $this->eventDispatcher->dispatch(new DumpEvent($dumper, $database, $configuration, $dumpContext));

            // Close the Doctrine connection (mysqldump-php uses its own connection)
            $database->close();

            if (!$dryRun) {
                // Create the dump
                $dumper->start($configuration->getDumpSettings()->getOutput());
            }

            $this->eventDispatcher->dispatch(new DumpFinishedEvent($configuration));
        } catch (Throwable $e) {
            isset($database) && $database->close();
            throw $e instanceof DumpException ? $e : new DumpException($e->getMessage(), $e);
        }
    }

    /**
     * Create the Mysqldump object.
     */
    private function createMysqldump(
        Database $database,
        Configuration $configuration,
        DumpContext $dumpContext,
    ): Mysqldump {

        $dumpSettings = $this->mysqldumpSettingsBuilder->build($configuration);

        // Set SQL variables
        $connection = $database->getConnection();
        foreach ($configuration->getVarQueries() as $varName => $query) {
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
