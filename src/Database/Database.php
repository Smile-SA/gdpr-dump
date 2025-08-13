<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception as DBALException;
use Smile\GdprDump\Database\Driver\DatabaseDriver;
use Smile\GdprDump\Database\Driver\MysqlDriver;
use Smile\GdprDump\Database\Exception\ConnectionException;
use Smile\GdprDump\Database\Exception\InvalidDriverException;
use Smile\GdprDump\Database\Metadata\DatabaseMetadata;
use Smile\GdprDump\Database\Metadata\MysqlMetadata;

/**
 * Wrapper that stores the following objects:
 * - connection: the Doctrine connection.
 * - driver: allows to retrieve the DSN that was used to connect to the database.
 * - metadata: allows to fetch the database metadata (table names, foreign key constraints).
 *
 * We use a custom abstraction layer for database metadata, because the Doctrine schema manager
 * crashes when used with databases that use custom Doctrine types (e.g. OroCommerce).
 * It also has poor performance.
 */
final class Database
{
    private Connection $connection;
    private DatabaseDriver $driver;
    private DatabaseMetadata $metadata;

    public function __construct(private ParameterBag $connectionParams)
    {
    }

    /**
     * Open the database connection.
     *
     * @throws DBALException
     */
    public function connect(): void
    {
        if ($this->isConnected()) {
            return;
        }

        $driverName = $this->connectionParams->get('driver');

        switch ($driverName) {
            case DatabaseDriver::MYSQL:
                $this->connection = DriverManager::getConnection($this->connectionParams->all());
                $this->driver = new MysqlDriver($this->connectionParams);
                $this->metadata = new MysqlMetadata($this->connection);
                break;

            default:
                throw new InvalidDriverException(sprintf('The database driver "%s" is not supported.', $driverName));
        }
    }

    /**
     * Close the database connection.
     */
    public function close(): void
    {
        if (isset($this->connection) && $this->connection->isConnected()) {
            $this->connection->close();
        }

        unset($this->connection);
        unset($this->driver);
        unset($this->metadata);
    }

    /**
     * Get the doctrine connection.
     */
    public function getConnection(): Connection
    {
        return $this->connection ?? throw new ConnectionException('The database connection is not opened');
    }

    /**
     * Get the database driver.
     */
    public function getDriver(): DatabaseDriver
    {
        return $this->driver ?? throw new ConnectionException('The database connection is not opened');
    }

    /**
     * Get the database metadata.
     */
    public function getMetadata(): DatabaseMetadata
    {
        return $this->metadata ?? throw new ConnectionException('The database connection is not opened');
    }

    /**
     * Get the connection parameters (host, port, user...).
     */
    public function getConnectionParams(): ParameterBag
    {
        return $this->connectionParams;
    }

    /**
     * Check whether the connection is opened.
     */
    private function isConnected(): bool
    {
        return isset($this->connection) && $this->connection->isConnected();
    }
}
