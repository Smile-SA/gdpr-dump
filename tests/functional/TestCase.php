<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Functional;

use PHPUnit\Framework\TestCase as BaseTestCase;
use RuntimeException;
use Smile\GdprDump\AppKernel;
use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Config\Loader\ConfigLoader;
use Smile\GdprDump\Database\Config as DatabaseConfig;
use Smile\GdprDump\Database\Database;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class TestCase extends BaseTestCase
{
    private static ?AppKernel $kernel = null;
    private static ?Database $database = null;

    /**
     * Get the absolute path of the application.
     *
     * @return string
     */
    protected static function getBasePath(): string
    {
        return dirname(__DIR__, 2);
    }

    /**
     * Get a resource file.
     *
     * @param string $fileName
     * @return string
     */
    protected static function getResource(string $fileName): string
    {
        return __DIR__ . '/Resources/' . $fileName;
    }

    /**
     * Get the database wrapper.
     *
     * @return Database
     */
    protected static function getDatabase(): Database
    {
        if (self::$database === null) {
            // Parse the config file
            /** @var ConfigLoader $loader */
            $loader = self::getContainer()->get('dumper.config_loader');
            $loader->load(self::getResource('config/templates/test.yaml'));

            /** @var Config $config */
            $config = self::getContainer()->get('dumper.config');
            $config->compile();

            // Initialize the shared connection
            $connectionParams = $config->get('database');
            $connectionParams['dbname'] = $connectionParams['name'];
            unset($connectionParams['name']);
            self::$database = new Database(new DatabaseConfig($connectionParams));

            // Create the tables
            $connection = self::$database->getConnection();
            $statement = $connection->prepare(self::getDatabaseDump());
            $statement->execute();
        }

        return self::$database;
    }

    /**
     * Get the DI container.
     *
     * @return ContainerInterface
     */
    protected static function getContainer(): ContainerInterface
    {
        if (self::$kernel === null) {
            self::$kernel = new AppKernel();
            self::$kernel->boot();
        }

        return self::$kernel->getContainer();
    }

    /**
     * Get the SQL queries that allow creating the test database.
     *
     * @return string
     */
    private static function getDatabaseDump(): string
    {
        $file = self::getResource('db/test.sql');
        $sql = file_get_contents($file);

        if ($sql === false) {
            throw new RuntimeException(sprintf('Failed to open the file "%s".', $file));
        }

        return $sql;
    }
}
