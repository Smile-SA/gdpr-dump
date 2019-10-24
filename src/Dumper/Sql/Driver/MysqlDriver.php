<?php
declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Sql\Driver;

use Doctrine\DBAL\Connection;

class MysqlDriver implements DriverInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string[]
     */
    private $params = [
        'host',
        'port',
        'dbname',
        'unix_socket',
        'charset',
    ];

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @inheritdoc
     */
    public function getDsn(): string
    {
        $values = $this->connection->getParams();
        $dsn = [];

        foreach ($this->params as $param) {
            if (!array_key_exists($param, $values)) {
                continue;
            }

            $value = $values[$param];
            if ($value !== '' && $value !== null) {
                $dsn[] = $param . '=' . $value;
            }
        }

        return 'mysql:' . implode(';', $dsn);
    }
}
