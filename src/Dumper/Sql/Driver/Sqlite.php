<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Dumper\Sql\Driver;

use Smile\Anonymizer\Dumper\Sql\Config\DatabaseConfig;

class Sqlite implements DriverInterface
{
    /**
     * @inheritdoc
     */
    public function getDsn(DatabaseConfig $config): string
    {
        $name = $config->getName();

        if ($name === 'memory') {
            $name = ':memory:';
        }

        return 'sqlite:' . $name;
    }
}
