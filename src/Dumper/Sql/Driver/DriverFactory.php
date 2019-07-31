<?php
declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Sql\Driver;

use UnexpectedValueException;

class DriverFactory
{
    /**
     * MySQL driver name.
     */
    const DRIVER_MYSQL = 'pdo_mysql';

    /**
     * @var string[]
     */
    public static $drivers = [
        self::DRIVER_MYSQL => Mysql::class,
    ];

    /**
     * Create a driver object.
     *
     * @param string $name
     * @return DriverInterface
     * @throws UnexpectedValueException
     */
    public static function create(string $name): DriverInterface
    {
        if (!array_key_exists($name, static::$drivers)) {
            throw new UnexpectedValueException(sprintf('The driver "%s" is not defined.', $name));
        }

        return new static::$drivers[$name];
    }
}
