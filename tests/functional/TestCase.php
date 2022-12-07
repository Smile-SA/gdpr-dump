<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Functional;

use PHPUnit\Framework\TestCase as BaseTestCase;
use RuntimeException;
use Smile\GdprDump\AppKernel;
use Smile\GdprDump\Config\Compiler\CompilerInterface;
use Smile\GdprDump\Config\ConfigInterface;
use Smile\GdprDump\Config\Loader\ConfigLoaderInterface;
use Smile\GdprDump\Database\Database;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class TestCase extends BaseTestCase
{
    private static ?AppKernel $kernel = null;
    private static ?Database $database = null;

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
            self::$kernel = new AppKernel();
            self::$kernel->boot();
            self::prepareContainer(self::$kernel->getContainer());
        }

        return self::$kernel->getContainer();
    }

    /**
     * Get the database wrapper.
     */
    protected static function getDatabase(): Database
    {
        if (self::$database === null) {
            /** @var ConfigInterface $config */
            $config = self::getContainer()->get('config');

            // Initialize the shared connection
            $connectionParams = $config->get('database');
            $connectionParams['dbname'] = $connectionParams['name'];
            unset($connectionParams['name']);
            self::$database = new Database($connectionParams);

            // Create the tables
            $connection = self::$database->getConnection();
            $statement = $connection->prepare(self::getDatabaseDump());
            $statement->execute();
        }

        return self::$database;
    }

    /**
     * Prepare the container.
     */
    private static function prepareContainer(ContainerInterface $container): void
    {
        /** @var ConfigLoaderInterface $loader */
        $loader = $container->get('config.loader');
        $loader->load(self::getResource('config/templates/test.yaml'));

        /** @var ConfigInterface $config */
        $config = $container->get('config');

        /** @var CompilerInterface $compiler */
        $compiler = $container->get('config.compiler');
        $compiler->compile($config);
    }

    /**
     * Get the SQL queries that allow creating the test database.
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
