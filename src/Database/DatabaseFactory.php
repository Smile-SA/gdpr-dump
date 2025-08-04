<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database;

use Doctrine\DBAL\Exception;
use Smile\GdprDump\Config\ConfigInterface;
use Smile\GdprDump\Database\Builder\ConnectionParamsBuilder;

final class DatabaseFactory
{
    public function __construct(private ConnectionParamsBuilder $connectionParamsBuilder)
    {
    }

    /**
     * Create a database object.
     *
     * @throws Exception
     */
    public function create(ConfigInterface $config): Database
    {
        $connectionParams = $this->connectionParamsBuilder->build($config);

        return new Database(new ParameterBag($connectionParams));
    }
}
