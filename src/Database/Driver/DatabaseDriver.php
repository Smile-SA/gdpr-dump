<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database\Driver;

interface DatabaseDriver
{
    public CONST MYSQL = 'pdo_mysql';

    /**
     * Get the data source name.
     */
    public function getDsn(): string;
}
