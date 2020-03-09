<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Config;

use UnexpectedValueException;

class DatabaseConfig
{
    /**
     * @var string
     */
    private $driver = 'pdo_mysql';

    /**
     * @var array
     */
    private $driverOptions = [];

    /**
     * @var array
     */
    private $connectionParams = [];

    /**
     * @var array
     */
    private $defaults = [
        'pdo_mysql' => ['host' => 'localhost', 'user' => 'root'],
    ];

    /**
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->prepareConfig($params);
    }

    /**
     * Get the database driver.
     *
     * @return string
     */
    public function getDriver(): string
    {
        return $this->driver;
    }

    /**
     * Get the driver options.
     *
     * @return array
     */
    public function getDriverOptions(): array
    {
        return $this->driverOptions;
    }

    /**
     * Get the connection parameters (host, port, user...).
     *
     * @return array
     */
    public function getConnectionParams(): array
    {
        return $this->connectionParams;
    }

    /**
     * Get the value of a connection parameter.
     *
     * @param string $name
     * @return mixed
     */
    public function getConnectionParam(string $name)
    {
        return $this->connectionParams[$name] ?? null;
    }

    /**
     * Prepare the database config.
     *
     * @param array $params
     * @throws UnexpectedValueException
     */
    private function prepareConfig(array $params)
    {
        // The database name is mandatory, no matter what driver is used
        // (this will require some refactoring if SQLite compatibility is added)
        if (!isset($params['name'])) {
            throw new UnexpectedValueException('Missing database name.');
        }

        // Set the driver
        if (isset($params['driver'])) {
            $this->driver = (string) $params['driver'];
            unset($params['driver']);
        }

        // Set the driver options (PDO settings)
        if (array_key_exists('driver_options', $params)) {
            $this->driverOptions = $params['driver_options'];
            unset($params['driver_options']);
        }

        // Set connection parameters values
        if (isset($this->defaults[$this->driver])) {
            $this->connectionParams = $this->defaults[$this->driver];
        }

        foreach ($params as $param => $value) {
            $this->connectionParams[$param] = (string) $value;
        }
    }
}
