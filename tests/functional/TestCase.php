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
    protected static ?AppKernel $kernel = null;
    protected static ?Database $database = null;

    /**
     * Boot the kernel.
     */
    protected static function bootKernel(): void
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
    protected static function bootDatabase(): void
    {
        // Use a shared connection to speed up the tests
        if (static::$database !== null) {
            return;
        }

        // Boot the kernel
        static::bootKernel();

        // Parse the config file
        /** @var ConfigLoader $loader */
        $loader = static::getContainer()->get('dumper.config_loader');
        $loader->load(static::getResource('config/templates/test.yaml'));

        /** @var Config $config */
        $config = static::getContainer()->get('dumper.config');
        $config->compile();

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
