<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper;

use Doctrine\DBAL\Exception as DBALException;
use Ifsnop\Mysqldump\Mysqldump;
use Smile\GdprDump\Config\ConfigInterface;
use Smile\GdprDump\Database\Config as DatabaseConfig;
use Smile\GdprDump\Database\Database;
use Smile\GdprDump\Dumper\Config\ConfigProcessor;
use Smile\GdprDump\Dumper\Config\DumperConfig;
use Smile\GdprDump\Dumper\Mysql\Context;
use Smile\GdprDump\Dumper\Mysql\ExtensionInterface;

class MysqlDumper implements DumperInterface
{
    /**
     * @var ExtensionInterface[]
     */
    private array $extensions;

    /**
     * @param ExtensionInterface[] $extensions
     */
    public function __construct(array $extensions = [])
    {
        $this->extensions = $extensions;
    }

    /**
     * @@inheritdoc
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

        // Create the MySQLDump object
        $dumper = new Mysqldump(
            $database->getDriver()->getDsn(),
            $database->getConfig()->getConnectionParam('user'),
            $database->getConfig()->getConnectionParam('password'),
            $dumpSettings,
            $database->getConfig()->getConnectionParam('driverOptions', [])
        );

        // Register extensions
        $extensionContext = new Context($dumper, $database, $config, $context);
        foreach ($this->extensions as $extension) {
            $extension->register($extensionContext);
        }

        // Unset the database object to close the database connection
        unset($database);

        // Create the dump
        $output = $config->getDumpOutput();
        $dumper->start($output);
    }

    /**
     * Create a database object.
     *
     * @param ConfigInterface $config
     * @return Database
     * @throws DBALException
     */
    private function getDatabase(ConfigInterface $config): Database
    {
        $params = $config->get('database', []);

        // Rename some keys (for compatibility with the Doctrine connection)
        if (array_key_exists('name', $params)) {
            $params['dbname'] = $params['name'];
            unset($params['name']);
        }

        if (array_key_exists('driver_options', $params)) {
            $params['driverOptions'] = $params['driver_options'];
            unset($params['driver_options']);
        }

        return new Database(new DatabaseConfig($params));
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
