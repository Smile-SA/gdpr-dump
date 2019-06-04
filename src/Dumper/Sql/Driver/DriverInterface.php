<?php
declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Sql\Driver;

use Smile\GdprDump\Dumper\Sql\Config\DatabaseConfig;

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
