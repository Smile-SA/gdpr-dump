<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper;

use Smile\GdprDump\Config\ConfigInterface;
use Smile\GdprDump\Database\DatabaseFactory;
use Smile\GdprDump\Dumper\Config\ConfigProcessor;
use Smile\GdprDump\Dumper\Config\DumperConfig;
use Smile\GdprDump\Dumper\Event\DumpEvent;
use Smile\GdprDump\Enum\DriversEnum;
use Spatie\DbDumper\Databases\PostgreSql;
use Symfony\Component\EventDispatcher\EventDispatcher;

readonly class PgsqlDumper implements DumperInterface
{
    public function __construct(
        private DatabaseFactory $databaseFactory,
        private EventDispatcher $eventDispatcher,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function dump(ConfigInterface $config): void
    {
        $database = $this->databaseFactory->create($config, DriversEnum::DRIVER_PGSQL->value);

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
        $dumper = (new PostgreSql())->setDatabaseUrl($database->getDriver()->getDsn());

        $event = new DumpEvent($dumper, $database, $config, $context);
        $this->eventDispatcher->dispatch($event);

        // Close the Doctrine connection before proceeding to the dump creation (PgDump-PHP uses its own connection)
        $database->getConnection()->close();

        // Create the dump
        $output = $config->getDumpOutput();
        $dumper->dumpToFile($output);
    }

    /**
     * Get the dump settings.
     */
    private function getDumpSettings(DumperConfig $config): array
    {
        $settings = $config->getDumpSettings();

        // Output setting is only used by our app
        unset($settings['output']);

        // PgSQLDump-PHP uses the '-' word separator for most settings
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

        return $settings;
    }
}