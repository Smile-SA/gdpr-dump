<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Functional;

use PHPUnit\Framework\TestCase as BaseTestCase;
use RuntimeException;
use Smile\GdprDump\Config\Compiler\CompilerInterface;
use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Config\ConfigInterface;
use Smile\GdprDump\Config\Loader\ConfigLoaderInterface;
use Smile\GdprDump\Database\Database;
use Smile\GdprDump\Kernel;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class TestCase extends BaseTestCase
{
    private static ?Kernel $kernel = null;
    private static ?Database $database = null;
    private static ?Config $config = null;

    /**
     * Get the absolute path of the application.
     */
    protected static function getBasePath(): string
    {
        return dirname(__DIR__, 2);
    }

    /**
     * Get a resource file.
     */
    protected static function getResource(string $fileName): string
    {
        return __DIR__ . '/Resources/' . $fileName;
    }

    /**
     * Get the DI container.
     */
    protected static function getContainer(): ContainerInterface
    {
        if (self::$kernel === null) {
            self::$kernel = new Kernel();
            self::$kernel->boot();
        }

        return self::$kernel->getContainer();
    }

    /**
     * Get the dumper config.
     */
    protected static function getConfig(): ConfigInterface
    {
        if (self::$config === null) {
            self::$config = new Config();

            /** @var ConfigLoaderInterface $loader */
            $loader = self::getContainer()->get('config.loader');
            $loader->load(self::getResource('config/templates/config.yaml'), self::$config);

            /** @var CompilerInterface $compiler */
            $compiler = self::getContainer()->get('config.compiler');
            $compiler->compile(self::$config);
        }

        return self::$config;
    }

    /**
     * Get the database wrapper.
     */
    protected static function getDatabase(): Database
    {
        if (self::$database === null) {
            $config = self::getConfig();

            // Initialize the shared connection
            $connectionParams = $config->get('database');
            $connectionParams['dbname'] = $connectionParams['name'];
            unset($connectionParams['name']);
            self::$database = new Database($connectionParams);

            // Create the tables
            $connection = self::$database->getConnection();
            $statement = $connection->prepare(self::getDatabaseDump());
            $statement->executeQuery();
        }

        return self::$database;
    }

    /**
     * Get the SQL queries that allow creating the test database.
     *
     * @throws RuntimeException
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
