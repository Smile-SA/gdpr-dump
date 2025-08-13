<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database;

use Doctrine\DBAL\Exception;

final class DatabaseFactory
{
    /**
     * Create a database object.
     *
     * @throws Exception
     */
    public function create(array $connectionParams): Database
    {
        return new Database(new ParameterBag($connectionParams));
    }
}
