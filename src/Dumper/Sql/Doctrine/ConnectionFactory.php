<?php
declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Sql\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Smile\GdprDump\Dumper\Sql\Config\DatabaseConfig;

class ConnectionFactory
{
    /**
     * Create a connection from the configuration.
     *
     * @param DatabaseConfig $config
     * @return Connection
     * @throws DBALException
     */
    public static function create(DatabaseConfig $config): Connection
    {
        $params = [
            'dbname' => $config->getDatabaseName(),
            'user' => $config->getUser(),
            'password' => $config->getPassword(),
            'host' => $config->getHost(),
            'port' => $config->getPort(),
            'driver' => $config->getDriver(),
        ];

        // Remove empty elements
        $params = array_filter($params, function ($value) {
            return $value !== null && $value !== '' && $value !== false;
        });

        /** @var Connection $connection */
        $connection = DriverManager::getConnection($params);

        return $connection;
    }
}
