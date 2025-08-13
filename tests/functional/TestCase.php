<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Functional;

use PHPUnit\Framework\TestCase as BaseTestCase;
use RuntimeException;
use Smile\GdprDump\Configuration\Configuration;
use Smile\GdprDump\Configuration\ConfigurationFactory;
use Smile\GdprDump\Configuration\Loader\Resource\Resource;
use Smile\GdprDump\Database\Database;
use Smile\GdprDump\Database\ParameterBag;
use Smile\GdprDump\Kernel;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class TestCase extends BaseTestCase
{
    private static Kernel $kernel;
    private static Database $database;
    private static Configuration $configuration;

    public static function setUpBeforeClass(): void
    {
        self::getDatabase()->connect();
    }

    public static function tearDownAfterClass(): void
    {
        self::getDatabase()->close();
    }

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
        if (!isset(self::$kernel)) {
            self::$kernel = new Kernel();
            self::$kernel->boot();
        }

        return self::$kernel->getContainer();
    }

    /**
     * Get the dumper config.
     */
    protected static function getConfiguration(): Configuration
    {
        if (!isset(self::$configuration)) {
            /** @var ConfigurationFactory $factory */
            $factory = self::getContainer()->get(ConfigurationFactory::class);
            $builder = $factory->createBuilder();
            $builder->addResource(new Resource(self::getResource('config/config.yaml')));
            self::$configuration = $builder->build();
        }

        return self::$configuration;
    }

    /**
     * Get the database wrapper.
     */
    protected static function getDatabase(): Database
    {
        if (!isset(self::$database)) {
            $configuration = self::getConfiguration();

            // Initialize the shared connection
            $connectionParams = $configuration->getConnectionParams();
            self::$database = new Database(new ParameterBag($connectionParams));
            self::$database->connect();

            // Create the tables
            $connection = self::$database->getConnection();
            $statement = $connection->prepare(self::getDatabaseDump());
            $statement->executeQuery();
            self::$database->close();
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
