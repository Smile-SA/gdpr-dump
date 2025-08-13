<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database;

use Doctrine\DBAL\Exception;
use Smile\GdprDump\Config\DumperConfig;

final class DatabaseFactory
{
    /**
     * Create a database object.
     *
     * @throws Exception
     */
    public function create(DumperConfig $config): Database
    {
        $connectionParams = $config->getConnectionParams();

        return new Database(new ParameterBag($connectionParams));
    }
}
