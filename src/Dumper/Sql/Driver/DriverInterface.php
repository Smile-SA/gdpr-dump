<?php
declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Sql\Driver;

interface DriverInterface
{
    /**
     * Get the data source name.
     *
     * @return string
     */
    public function getDsn(): string;
}
