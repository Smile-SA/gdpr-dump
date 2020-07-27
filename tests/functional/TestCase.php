<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Functional;

use PHPUnit\Framework\TestCase as BaseTestCase;
use RuntimeException;
use Smile\GdprDump\AppKernel;
use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Config\ConfigLoader;
use Smile\GdprDump\Database\Database;
use Smile\GdprDump\Dumper\Config\DatabaseConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class TestCase extends BaseTestCase
{
    /**
     * @var AppKernel
     */
    protected static $kernel;

    /**
     * @var Database
     */
    protected static $database;

    /**
     * Boot the kernel.
     */
    protected static function bootKernel()
    {
        if (static::$kernel !== null) {
            return;
        }

        static::$kernel = new AppKernel();
        static::$kernel->boot();
    }

    /**
     * Boot the database.
     */
    protected static function bootDatabase()
    {
        // Use a shared connection to speed up the tests
        if (static::$database !== null) {
            return;
        }

        // Boot the kernel
        static::bootKernel();

        // Parse the config file
        /** @var ConfigLoader $loader */
        $loader = static::getContainer()->get(ConfigLoader::class);
        $loader->loadFile(static::getResource('config/templates/test.yaml'));
        /** @var Config $config */
        $config = static::getContainer()->get(Config::class);

        // Initialize the shared connection
        $dbParams = $config->get('database');
        static::$database = new Database(new DatabaseConfig($dbParams));

        // Create the tables
        $connection = static::$database->getConnection();
        $queries = file_get_contents(static::getResource('db/test.sql'));
        $statement = $connection->prepare($queries);
        $statement->execute();
    }

    /**
     * Get the absolute path of the application.
     *
     * @return string
     */
    protected static function getBasePath(): string
    {
        return dirname(dirname(__DIR__));
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
     * @throws RuntimeException
     */
    protected static function getDatabase(): Database
    {
        if (static::$database === null) {
            throw new RuntimeException('The database is not initialized.');
        }

        return static::$database;
    }

    /**
     * Get the DI container.
     *
     * @return ContainerInterface
     * @throws RuntimeException
     */
    protected static function getContainer(): ContainerInterface
    {
        if (static::$kernel === null) {
            throw new RuntimeException('The kernel is not initialized.');
        }

        return static::$kernel->getContainer();
    }
}
