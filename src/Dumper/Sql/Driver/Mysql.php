<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Dumper\Sql\Driver;

use Smile\Anonymizer\Dumper\Sql\Config\DatabaseConfig;

class Mysql implements DriverInterface
{
    /**
     * @var string[]
     */
    private $params = [
        'host', 'port', 'name' => 'dbname', 'unix_socket', 'charset'
    ];

    /**
     * @inheritdoc
     */
    public function getDsn(DatabaseConfig $config): string
    {
        $values = $config->toArray();
        $dsn = [];

        foreach ($this->params as $configParam => $driverParam) {
            if (!is_string($configParam)) {
                $configParam = $driverParam;
            }

            if (!array_key_exists($configParam, $values)) {
                continue;
            }

            $value = $values[$configParam];
            if ($value !== '' && $value !== null) {
                $dsn[] = $driverParam . '=' . $value;
            }
        }

        return 'mysql:' . implode(';', $dsn);
    }
}
