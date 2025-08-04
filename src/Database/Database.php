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

final class Database implements DatabaseInterface
{
    private Connection $connection;
    private DriverInterface $driver;
    private MetadataInterface $metadata;

    /**
     * @throws Exception|UnexpectedValueException
     */
    public function __construct(private ParameterBag $connectionParams)
    {
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

    public function getConnection(): Connection
    {
        return $this->connection;
    }

    public function getDriver(): DriverInterface
    {
        return $this->driver;
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->metadata;
    }

    public function getConnectionParams(): ParameterBag
    {
        return $this->connectionParams;
    }
}
