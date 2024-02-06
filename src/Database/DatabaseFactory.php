<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database;

use Smile\GdprDump\Config\ConfigInterface;

class DatabaseFactory
{
    /**
     * Create a database object.
     */
    public function create(ConfigInterface $config): Database
    {
        $connectionParams = $config->get('database', []);

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
