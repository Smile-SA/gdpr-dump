<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database;

use UnexpectedValueException;

class Config implements ConfigInterface
{
    private const DEFAULT_DRIVER = 'pdo_mysql';

    private array $connectionParams = [];
    private array $defaults = [
        'pdo_mysql' => ['host' => 'localhost', 'user' => 'root'],
    ];

    /**
     * @param array $connectionParams
     */
    public function __construct(array $connectionParams)
    {
        $this->prepareConfig($connectionParams);
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
    public function getConnectionParam(string $name, $default = null)
    {
        return $this->connectionParams[$name] ?? $default;
    }

    /**
     * Prepare the database config.
     *
     * @param array $connectionParams
     * @throws UnexpectedValueException
     */
    private function prepareConfig(array $connectionParams): void
    {
        // The database name is mandatory, no matter what driver is used
        // (this will require some refactoring if SQLite compatibility is added)
        if (!isset($connectionParams['dbname'])) {
            throw new UnexpectedValueException('Missing database name.');
        }

        // Set the driver
        if (!isset($connectionParams['driver'])) {
            $connectionParams['driver'] = self::DEFAULT_DRIVER;
        }

        if (isset($this->defaults[$connectionParams['driver']])) {
            $connectionParams += $this->defaults[$connectionParams['driver']];
        }

        $this->connectionParams = $connectionParams;
    }
}
