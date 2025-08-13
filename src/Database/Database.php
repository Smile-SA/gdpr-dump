<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Smile\GdprDump\Database\Driver\DatabaseDriver;
use Smile\GdprDump\Database\Driver\MysqlDriver;
use Smile\GdprDump\Database\Exception\DatabaseException;
use Smile\GdprDump\Database\Metadata\DatabaseMetadata;
use Smile\GdprDump\Database\Metadata\MysqlMetadata;
use Throwable;

final class Database implements ConnectionProvider
{
    private Connection $connection;
    private DatabaseDriver $driver;
    private DatabaseMetadata $metadata;

    public function __construct(private ParameterBag $connectionParams)
    {
    }

    public function connect(): void
    {
        if ($this->isConnected()) {
            return;
        }

        $driverName = $this->connectionParams->get('driver');

        switch ($driverName) {
            case DatabaseDriver::MYSQL:
                $this->connection = $this->createConnection();
                $this->driver = new MysqlDriver($this->connectionParams);
                $this->metadata = new MysqlMetadata($this->connection);
                break;

            default:
                throw new DatabaseException(sprintf('The database driver "%s" is not supported.', $driverName));
        }
    }

    public function close(): void
    {
        if (isset($this->connection) && $this->connection->isConnected()) {
            $this->connection->close();
        }

        unset($this->connection);
        unset($this->driver);
        unset($this->metadata);
    }

    public function getConnection(): Connection
    {
        return $this->connection ?? throw new DatabaseException('The database connection is not opened');
    }

    public function getDriver(): DatabaseDriver
    {
        return $this->driver ?? throw new DatabaseException('The database connection is not opened');
    }

    public function getMetadata(): DatabaseMetadata
    {
        return $this->metadata ?? throw new DatabaseException('The database connection is not opened');
    }

    public function getConnectionParams(): ParameterBag
    {
        return $this->connectionParams ?? throw new DatabaseException('The database connection is not opened');
    }

    /**
     * Create a new connection.
     */
    private function createConnection(): Connection
    {
        try {
            return DriverManager::getConnection($this->connectionParams->all());
        } catch (Throwable $e) {
            throw new DatabaseException($e->getMessage(), $e);
        }
    }

    /**
     * Check whether the connection is opened.
     */
    private function isConnected(): bool
    {
        return isset($this->connection) && $this->connection->isConnected();
    }
}
