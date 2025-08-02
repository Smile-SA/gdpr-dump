<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper;

use Druidfi\Mysqldump\Mysqldump;
use Smile\GdprDump\Config\ConfigInterface;
use Smile\GdprDump\Database\DatabaseFactory;
use Smile\GdprDump\Dumper\Config\ConfigProcessor;
use Smile\GdprDump\Dumper\Config\DumperConfig;
use Smile\GdprDump\Dumper\Config\DumperConfigInterface;
use Smile\GdprDump\Dumper\Event\DumpEvent;
use Smile\GdprDump\Dumper\Event\DumpFinishedEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class MysqlDumper implements DumperInterface
{
    public function __construct(
        private DatabaseFactory $databaseFactory,
        private EventDispatcherInterface $eventDispatcher,
        private DumpContext $dumpContext,
    ) {
    }

    public function dump(ConfigInterface $config, bool $dryRun = false): void
    {
        $database = $this->databaseFactory->create($config);

        // Process tables declared in the configuration (remove undefined tables, resolve patterns such as "log_*")
        $processor = new ConfigProcessor($database->getMetadata());
        $processor->process($config);

        // Convert the config into an object with getters/setters
        $config = new DumperConfig($config);

        // Set the SQL variables
        $connection = $database->getConnection();
        $dumpSettings = $this->getDumpSettings($config);
        $this->dumpContext->variables = [];

        foreach ($config->getVarQueries() as $varName => $query) {
            $value = $connection->fetchOne($query);
            $this->dumpContext->variables[$varName] = $value;
            $dumpSettings['init_commands'][] = 'SET @' . $varName . ' = ' . $connection->quote($value);
        }

        // Create the MySQLDump-PHP object
        $dumper = new Mysqldump(
            $database->getDriver()->getDsn(),
            $database->getConnectionParams()->get('user'),
            $database->getConnectionParams()->get('password'),
            $dumpSettings,
            $database->getConnectionParams()->get('driverOptions', [])
        );

        $this->eventDispatcher->dispatch(new DumpEvent($dumper, $database, $config, $this->dumpContext));

        // Close the Doctrine connection before proceeding to the dump creation (MySQLDump-PHP uses its own connection)
        $database->getConnection()->close();

        if (!$dryRun) {
            // Create the dump
            $dumper->start($config->getDumpOutput());
        }

        $this->eventDispatcher->dispatch(new DumpFinishedEvent($config));
    }

    /**
     * Get the dump settings.
     */
    private function getDumpSettings(DumperConfigInterface $config): array
    {
        $settings = $config->getDumpSettings();

        // MySQLDump-PHP uses the '-' word separator for most settings
        foreach ($settings as $key => $value) {
            if ($key !== 'init_commands' && $key !== 'net_buffer_length') {
                $newKey = str_replace('_', '-', $key);

                if ($newKey !== $key) {
                    $settings[$newKey] = $value;
                    unset($settings[$key]);
                }
            }
        }

        if (array_key_exists('compress', $settings)) {
            // e.g. "gzip" -> "Gzip"
            $settings['compress'] = strtoupper($settings['compress']);
        }

        // Tables to include/exclude/truncate
        $settings['include-tables'] = $config->getIncludedTables();
        $settings['exclude-tables'] = $config->getExcludedTables();
        $settings['no-data'] = $config->getTablesToTruncate();

        // Set readonly session
        $settings['init_commands'][] = 'SET SESSION TRANSACTION READ ONLY';

        return $settings;
    }
}
