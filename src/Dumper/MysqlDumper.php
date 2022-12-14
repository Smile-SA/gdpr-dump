<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper;

use Doctrine\DBAL\Exception as DBALException;
use Druidfi\Mysqldump\Mysqldump;
use Smile\GdprDump\Config\ConfigInterface;
use Smile\GdprDump\Database\Database;
use Smile\GdprDump\Dumper\Config\ConfigProcessor;
use Smile\GdprDump\Dumper\Config\DumperConfig;
use Smile\GdprDump\Dumper\Mysql\Context;
use Smile\GdprDump\Dumper\Mysql\ExtensionInterface;

class MysqlDumper implements DumperInterface
{
    /**
     * @param ExtensionInterface[] $extensions
     */
    public function __construct(private iterable $extensions = [])
    {
    }

    /**
     * @inheritdoc
     */
    public function dump(ConfigInterface $config): void
    {
        // Process the configuration
        $database = $this->getDatabase($config);
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

        // Register extensions
        $extensionContext = new Context($dumper, $database, $config, $context);
        foreach ($this->extensions as $extension) {
            $extension->register($extensionContext);
        }

        // Close the Doctrine connection before proceeding to the dump creation (MySQLDump-PHP uses its own connection)
        $database->getConnection()->close();

        // Create the dump
        $output = $config->getDumpOutput();
        $dumper->start($output);
    }

    /**
     * Create a database object.
     *
     * @throws DBALException
     */
    private function getDatabase(ConfigInterface $config): Database
    {
        $connectionParams = $config->get('database', []);

        // Rename some keys (for compatibility with the Doctrine connection)
        if (array_key_exists('name', $connectionParams)) {
            $connectionParams['dbname'] = $connectionParams['name'];
            unset($connectionParams['name']);
        }

        if (array_key_exists('driver_options', $connectionParams)) {
            $connectionParams['driverOptions'] = $connectionParams['driver_options'];
            unset($connectionParams['driver_options']);
        }

        return new Database($connectionParams);
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

        return $settings;
    }
}
