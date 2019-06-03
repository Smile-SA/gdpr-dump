<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Dumper\Sql\Driver;

use Smile\Anonymizer\Dumper\Sql\Config\DatabaseConfig;

interface DriverInterface
{
    /**
     * Get the DSN.
     *
     * @param DatabaseConfig $config
     * @return string
     */
    public function getDsn(DatabaseConfig $config): string;
}
