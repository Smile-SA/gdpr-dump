<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database\Driver;

use Smile\GdprDump\Database\ConfigInterface;

class MysqlDriver implements DriverInterface
{
    private ConfigInterface $config;

    /**
     * @var string[]
     */
    private array $params = [
        'host',
        'port',
        'dbname',
        'unix_socket',
        'charset',
    ];

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function getDsn(): string
    {
        $values = $this->config->getConnectionParams();
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
