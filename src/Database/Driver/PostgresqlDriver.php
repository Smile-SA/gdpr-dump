<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database\Driver;

use Smile\GdprDump\Database\ParameterBag;

class PostgresqlDriver implements DriverInterface
{
    public function __construct(private readonly ParameterBag $connectionParams)
    {
    }

    /**
     * @inheritdoc
     */
    public function getDsn(): string
    {
        $connectionParams = $this->connectionParams->all();
        $dsn = [];

        foreach (['host', 'port', 'dbname', 'unix_socket', 'charset'] as $param) {
            if (!array_key_exists($param, $connectionParams)) {
                continue;
            }

            $value = $connectionParams[$param];
            if ($value !== '' && $value !== null) {
                $dsn[] = $param . '=' . $value;
            }
        }

        return 'postgresql:' . implode(';', $dsn);
    }
}
