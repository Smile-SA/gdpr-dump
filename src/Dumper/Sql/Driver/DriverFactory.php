<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Dumper\Sql\Driver;

class DriverFactory
{
    /**
     * @var string[]
     */
    public static $drivers = [
        'pdo_mysql' => Mysql::class,
        'pdo_sqlite' => Sqlite::class,
    ];

    /**
     * Create a driver object.
     *
     * @param string $name
     * @return DriverInterface
     * @throws \UnexpectedValueException
     */
    public static function create(string $name): DriverInterface
    {
        if (!array_key_exists($name, static::$drivers)) {
            throw new \UnexpectedValueException(sprintf('The driver "%s" is not defined.', $name));
        }

        return new static::$drivers[$name];
    }
}
