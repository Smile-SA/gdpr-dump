<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper;

use Druidfi\Mysqldump\Mysqldump;
use Smile\GdprDump\Config\ConfigInterface;
use Smile\GdprDump\Database\DatabaseFactory;
use Smile\GdprDump\Dumper\Config\ConfigProcessor;
use Smile\GdprDump\Dumper\Config\DumperConfig;
use Smile\GdprDump\Dumper\Event\DumpEvent;
use Smile\GdprDump\Dumper\Event\DumpFinishedEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class MysqlDumper implements DumperInterface
{
    public function __construct(
        private DatabaseFactory $databaseFactory,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * @inheritdoc
     */
    public function dump(ConfigInterface $config): void
    {
        $database = $this->databaseFactory->create($config);

        // Process the configuration
        $processor = new ConfigProcessor($database->getMetadata());
        $config = $processor->process($config);

        // Set the SQL variables
        $connection = $database->getConnection();
        $dumpSettings = $this->getDumpSettings($config);
        $context = ['vars' => []];

        foreach ($config->getVarQueries() as $varName => $query) {
            $value = $connection->fetchOne($query);
            $context['vars'][$varName] = $value;
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

        $this->eventDispatcher->dispatch(new DumpEvent($dumper, $database, $config, $context));

        // Close the Doctrine connection before proceeding to the dump creation (MySQLDump-PHP uses its own connection)
        $database->getConnection()->close();

        // Create the dump
        $output = $config->getDumpOutput();
        $dumper->start($output);
        $this->eventDispatcher->dispatch(new DumpFinishedEvent($config));
    }

    /**
     * Get the dump settings.
     */
    private function getDumpSettings(DumperConfig $config): array
    {
        $settings = $config->getDumpSettings();

        // Output setting is only used by our app
        unset($settings['output']);

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

        // Tables to whitelist/blacklist/truncate
        $settings['include-tables'] = $config->getTablesWhitelist();
        $settings['exclude-tables'] = $config->getTablesBlacklist();
        $settings['no-data'] = $config->getTablesToTruncate();

        // Set readonly session
        $settings['init_commands'][] = 'SET SESSION TRANSACTION READ ONLY';

        return $settings;
    }
}
