<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database\Driver;

interface DatabaseDriver
{
    public const MYSQL = 'pdo_mysql';
    public const DEFAULT = self::MYSQL;

    /**
     * Get the data source name.
     */
    public function getDsn(): string;
}
