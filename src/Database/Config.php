<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database;

use UnexpectedValueException;

class Config implements ConfigInterface
{
    private string $driver = 'pdo_mysql';
    private array $driverOptions = [];
    private array $connectionParams = [];
    private array $defaults = [
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
     * @inheritdoc
     */
    public function getDriver(): string
    {
        return $this->driver;
    }

    /**
     * @inheritdoc
     */
    public function getDriverOptions(): array
    {
        return $this->driverOptions;
    }

    /**
     * @inheritdoc
     */
    public function getConnectionParams(): array
    {
        return $this->connectionParams;
    }

    /**
     * @inheritdoc
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
    private function prepareConfig(array $params): void
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
