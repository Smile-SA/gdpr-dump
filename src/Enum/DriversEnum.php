<?php

declare(strict_types=1);

namespace Smile\GdprDump\Enum;

enum DriversEnum: string
{
    case DRIVER_MYSQL = 'pdo_mysql';
    case DRIVER_PGSQL = 'pdo_pgsql';
}
