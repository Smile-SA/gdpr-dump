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
 * - connection: the Doctrine connection.
 * - driver: allows to retrieve the DSN that was used to connect to the database.
 * - metadata: allows to fetch the database metadata (table names, foreign key constraints).
 *
 * We use a custom abstraction layer for database metadata, because the Doctrine schema manager
 * crashes when used with databases that use custom Doctrine types (e.g. OroCommerce).
 */
class Database
{
    public const DRIVER_MYSQL = 'pdo_mysql';

    private Connection $connection;
    private DriverInterface $driver;
    private MetadataInterface $metadata;
    private ParameterBag $connectionParams;

    /**
     * @throws Exception|UnexpectedValueException
     */
    public function __construct(array $connectionParams)
    {
        $this->connectionParams = new ParameterBag($connectionParams);
        $this->connection = DriverManager::getConnection($this->connectionParams->all());

        $driver = $this->connectionParams->get('driver');

        switch ($driver) {
            case self::DRIVER_MYSQL:
                $this->driver = new MysqlDriver($this->connectionParams);
                $this->metadata = new MysqlMetadata($this->connection);
                break;

            default:
                throw new UnexpectedValueException(sprintf('The database driver "%s" is not supported.', $driver));
        }
    }

    /**
     * Get the doctrine connection.
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * Get the database driver.
     */
    public function getDriver(): DriverInterface
    {
        return $this->driver;
    }

    /**
     * Get the database metadata.
     */
    public function getMetadata(): MetadataInterface
    {
        return $this->metadata;
    }

    /**
     * Get the connection parameters (host, port, user...).
     */
    public function getConnectionParams(): ParameterBag
    {
        return $this->connectionParams;
    }
}
