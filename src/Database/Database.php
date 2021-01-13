<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Smile\GdprDump\Database\Driver\DriverInterface;
use Smile\GdprDump\Database\Driver\MysqlDriver;
use Smile\GdprDump\Database\Metadata\MetadataInterface;
use Smile\GdprDump\Database\Metadata\MysqlMetadata;
use UnexpectedValueException;

/**
 * Wrapper that stores the following objects:
 *
 * - connection: the Doctrine connection
 * - driver: allows to retrieve the DSN that was used to connect to the database
 * - metadata: allows to fetch the database metadata (table names, foreign key constraints)
 *
 * We use a custom abstraction layer for database metadata, because the Doctrine schema manager
 * crashes when used with databases that use custom Doctrine types (e.g. OroCommerce).
 */
class Database implements DatabaseInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @var MetadataInterface
     */
    private $metadata;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param ConfigInterface $config
     * @throws Exception
     * @throws UnexpectedValueException
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
        $this->connection = $this->createConnection($config);
        $driver = $config->getDriver();

        switch ($driver) {
            case 'pdo_mysql':
                $this->driver = new MysqlDriver($this->connection);
                $this->metadata = new MysqlMetadata($this->connection);
                break;

            default:
                throw new UnexpectedValueException(sprintf('The database driver "%s" is not supported.', $driver));
        }
    }

    /**
     * Destruct the database object.
     */
    public function __destruct()
    {
        $this->connection->close();
    }

    /**
     * @inheritdoc
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * @inheritdoc
     */
    public function getDriver(): DriverInterface
    {
        return $this->driver;
    }

    /**
     * @inheritdoc
     */
    public function getMetadata(): MetadataInterface
    {
        return $this->metadata;
    }

    /**
     * @inheritdoc
     */
    public function getConfig(): ConfigInterface
    {
        return $this->config;
    }

    /**
     * Create a Doctrine connection.
     *
     * @param ConfigInterface $config
     * @return Connection
     * @throws Exception
     */
    private function createConnection(ConfigInterface $config): Connection
    {
        // Get the connection parameters from the config
        $params = $config->getConnectionParams();

        // Rename parameters that do not match Doctrine naming conventions (name -> dbname)
        $params['dbname'] = $params['name'];
        unset($params['name']);

        // Remove empty elements
        $params = array_filter($params, function ($value): bool {
            return $value !== null && $value !== '' && $value !== false;
        });

        // Set the driver
        $params['driver'] = $config->getDriver();
        $params['driverOptions'] = $config->getDriverOptions();

        return DriverManager::getConnection($params);
    }
}
