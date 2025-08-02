<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper;

use Druidfi\Mysqldump\Mysqldump;
use Smile\GdprDump\Config\ConfigInterface;
use Smile\GdprDump\Database\DatabaseFactory;
use Smile\GdprDump\Database\DatabaseInterface;
use Smile\GdprDump\Database\Metadata\MetadataInterface;
use Smile\GdprDump\Dumper\Builder\MysqldumpSettingsBuilder;
use Smile\GdprDump\Dumper\Config\ConfigProcessor;
use Smile\GdprDump\Dumper\Config\DumperConfig;
use Smile\GdprDump\Dumper\Event\DumpEvent;
use Smile\GdprDump\Dumper\Event\DumpFinishedEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class MysqlDumper implements DumperInterface
{
    public function __construct(
        private DatabaseFactory $databaseFactory,
        private MysqldumpSettingsBuilder $mysqldumpSettingsBuilder,
        private DumpContext $dumpContext,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function dump(ConfigInterface $config, bool $dryRun = false): void
    {
        $this->dumpContext->reset();

        // Initialize the database connection
        $database = $this->databaseFactory->create($config);

        // Convert the configuration to an object with getters/setters
        $config = $this->createDumperConfig($config, $database->getMetadata());

        // Create the Mysqldump object (mysqldump-php library)
        $dumper = $this->createMysqldump($database, $config);

        $this->eventDispatcher->dispatch(new DumpEvent($dumper, $database, $config));

        // Close the Doctrine connection before proceeding to the dump creation (mysqldump-php uses its own connection)
        $database->getConnection()->close();

        if (!$dryRun) {
            // Create the dump
            $dumper->start($config->getDumpOutput());
        }

        $this->eventDispatcher->dispatch(new DumpFinishedEvent($config));
    }

    /**
     * Create the dumper config object.
     */
    private function createDumperConfig(ConfigInterface $config, MetadataInterface $metadata): DumperConfig
    {
        // Process tables declared in the configuration (remove undefined tables, resolve patterns such as "log_*")
        $processor = new ConfigProcessor($metadata);
        $processor->process($config);

        return new DumperConfig($config);
    }

    /**
     * Create the Mysqldump object.
     */
    private function createMysqldump(DatabaseInterface $database, DumperConfig $config): Mysqldump
    {
        $dumpSettings = $this->mysqldumpSettingsBuilder->build($config);

        // Set SQL variables
        $connection = $database->getConnection();
        foreach ($config->getVarQueries() as $varName => $query) {
            $value = $connection->fetchOne($query);
            $this->dumpContext->variables[$varName] = $value;
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
