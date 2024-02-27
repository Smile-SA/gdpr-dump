<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database;

use Doctrine\DBAL\Exception;
use Smile\GdprDump\Config\ConfigInterface;
use Smile\GdprDump\Enum\DriversEnum;

class DatabaseFactory
{
    /**
     * Create a database object.
     *
     * @throws Exception
     */
    public function create(ConfigInterface $config, string $driver): Database
    {
        $connectionParams = $config->get('database', []);
        $connectionParams['driver'] = $driver;
        // Rename some keys (for compatibility with the Doctrine connection)
        if (array_key_exists('name', $connectionParams)) {
            $connectionParams['dbname'] = $connectionParams['name'];
            unset($connectionParams['name']);
        }

        if (array_key_exists('driver_options', $connectionParams)) {
            $connectionParams['driverOptions'] = $connectionParams['driver_options'];
            unset($connectionParams['driver_options']);
        }

        return new Database($connectionParams);
    }
}
