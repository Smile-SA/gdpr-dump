<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper;

use Ifsnop\Mysqldump\Mysqldump;
use Smile\GdprDump\Config\ConfigInterface;
use Smile\GdprDump\Converter\ConverterFactory;
use Smile\GdprDump\Database\Database;
use Smile\GdprDump\Dumper\Config\ConfigProcessor;
use Smile\GdprDump\Dumper\Config\DatabaseConfig;
use Smile\GdprDump\Dumper\Config\DumperConfig;
use Smile\GdprDump\Dumper\Mysqldump\DataConverterExtension;
use Smile\GdprDump\Dumper\Mysqldump\TableFilterExtension;

class SqlDumper implements DumperInterface
{
    /**
     * @var ConverterFactory
     */
    private $converterFactory;

    /**
     * @param ConverterFactory $converterFactory
     */
    public function __construct(ConverterFactory $converterFactory)
    {
        $this->converterFactory = $converterFactory;
    }

    /**
     * @@inheritdoc
     */
    public function dump(ConfigInterface $config): DumperInterface
    {
        // Create the database connection
        $databaseConfig = new DatabaseConfig($config->get('database', []));
        $database = new Database($databaseConfig);

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

            // This is only compatible with MySQL and will require refactoring to add compatibility with other drivers
            $dumpSettings['init_commands'][] = 'SET @' . $varName . ' = ' . $connection->quote($value);
        }

        // Create the MySQLDump object
        $dumper = new Mysqldump(
            $database->getDriver()->getDsn(),
            $databaseConfig->getConnectionParam('user'),
            $databaseConfig->getConnectionParam('password'),
            $dumpSettings,
            $databaseConfig->getDriverOptions()
        );

        // Register a data conversion extension
        $dataConverterExtension = new DataConverterExtension($config, $this->converterFactory, $context);
        $dataConverterExtension->register($dumper);

        // Register a table filter extension
        $tableFilterExtension = new TableFilterExtension($database, $config);
        $tableFilterExtension->register($dumper);

        // Unset the database object to close the database connection
        unset($database);

        // Create the dump
        $output = $config->getDumpOutput();
        $dumper->start($output);

        return $this;
    }

    /**
     * Get the dump settings.
     *
     * @param DumperConfig $config
     * @return array
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

        return $settings;
    }
}
