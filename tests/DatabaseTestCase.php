<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests;

use Doctrine\DBAL\Connection;
use Smile\GdprDump\Dumper\Sql\Config\DatabaseConfig;
use Smile\GdprDump\Dumper\Sql\Doctrine\ConnectionFactory;
use Symfony\Component\Yaml\Yaml;

abstract class DatabaseTestCase extends TestCase
{
    /**
     * @var Connection
     */
    protected static $connection;

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass()
    {
        if (static::skipDatabaseTests()) {
            static::markTestSkipped('Skip database tests.');
        }

        // Use a shared connection to speed up the tests
        if (static::$connection !== null) {
            return;
        }

        // Create the shared connection
        $config = Yaml::parseFile(static::getTestConfigFile());
        static::$connection = ConnectionFactory::create(new DatabaseConfig($config['database']));

        // Create the tables
        $queries = file_get_contents(static::getResource('db/test.sql'));
        $statement = static::$connection->prepare($queries);
        $statement->execute();
    }

    /**
     * Check if the database tests should be performed.
     *
     * @return bool
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function skipDatabaseTests(): bool
    {
        return (bool) $GLOBALS['skip_database_tests'];
    }

    /**
     * Get the connection object.
     *
     * @return Connection
     */
    protected function getConnection(): Connection
    {
        return static::$connection;
    }
}
