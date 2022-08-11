<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database\Driver;

interface DriverInterface
{
    /**
     * Get the data source name.
     */
    public function getDsn(): string;
}
