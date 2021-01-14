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
use Smile\GdprDump\Dumper\Mysqldump\Context;
use Smile\GdprDump\Dumper\Mysqldump\ExtensionInterface;

class SqlDumper implements DumperInterface
{
    /**
     * @var ExtensionInterface[]
     */
    private $extensions;

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
    public function dump(ConfigInterface $config): DumperInterface
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

            // This is only compatible with MySQL and will require refactoring to add compatibility with other drivers
            $dumpSettings['init_commands'][] = 'SET @' . $varName . ' = ' . $connection->quote($value);
        }

        // Create the MySQLDump object
        $dumper = new Mysqldump(
            $database->getDriver()->getDsn(),
            $database->getConfig()->getConnectionParam('user'),
            $database->getConfig()->getConnectionParam('password'),
            $dumpSettings,
            $database->getConfig()->getDriverOptions()
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

        return $this;
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
        $databaseConfig = new DatabaseConfig($config->get('database', []));

        return new Database($databaseConfig);
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
