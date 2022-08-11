<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database\Driver;

use Smile\GdprDump\Database\ParameterBag;

class MysqlDriver implements DriverInterface
{
    public function __construct(private ParameterBag $connectionParams)
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

        return 'mysql:' . implode(';', $dsn);
    }
}
