<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Functional;

use Smile\GdprDump\Database\Database;
use Smile\GdprDump\Dumper\Config\DatabaseConfig;
use Symfony\Component\Yaml\Yaml;

abstract class DatabaseTestCase extends TestCase
{
    /**
     * @var Database
     */
    protected static $database;

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass()
    {
        if (static::skipDatabaseTests()) {
            static::markTestSkipped('Skip database tests.');
        }

        // Use a shared connection to speed up the tests
        if (static::$database !== null) {
            return;
        }

        // Create the shared connection
        $databaseInfo = Yaml::parseFile(static::getTestConfigFile());
        $config = new DatabaseConfig($databaseInfo['database']);
        static::$database = new Database($config);

        // Create the tables
        $connection = static::$database->getConnection();
        $queries = file_get_contents(static::getResource('db/test.sql'));
        $statement = $connection->prepare($queries);
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
     * Get the database wrapper.
     *
     * @return Database
     */
    protected function getDatabase(): Database
    {
        return static::$database;
    }
}
