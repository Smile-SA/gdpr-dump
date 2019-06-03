<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Dumper\Sql\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Smile\Anonymizer\Dumper\Sql\Config\DatabaseConfig;

class ConnectionFactory
{
    /**
     * Create a connection from the configuration.
     *
     * @param DatabaseConfig $config
     * @return Connection
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function create(DatabaseConfig $config): Connection
    {
        $params = [
            'dbname' => $config->getDatabaseName(),
            'user' => $config->getUser(),
            'password' => $config->getPassword(),
            'host' => $config->getHost(),
            'driver' => $config->getDriver(),
        ];

        /** @var Connection $connection */
        $connection = DriverManager::getConnection($params);

        return $connection;
    }
}
