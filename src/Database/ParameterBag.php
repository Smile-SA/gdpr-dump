<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database;

use UnexpectedValueException;

class ParameterBag
{
    private array $params;
    private array $defaults = [
        Database::DRIVER_MYSQL => ['host' => 'localhost', 'user' => 'root'],
    ];

    /**
     * @throws UnexpectedValueException
     */
    public function __construct(array $params)
    {
        $this->params = $this->prepareParams($params);
    }

    /**
     * Get all parameters.
     */
    public function all(): array
    {
        return $this->params;
    }

    /**
     * Get the value of a parameter
     */
    public function get(string $name, mixed $default = null): mixed
    {
        return $this->params[$name] ?? $default;
    }

    /**
     * Prepare the connection params.
     *
     * @throws UnexpectedValueException
     */
    private function prepareParams(array $params): array
    {
        // The database name is mandatory, no matter what driver is used
        // (this will require some refactoring if SQLite compatibility is added)
        if (!isset($params['dbname'])) {
            throw new UnexpectedValueException('Missing database name.');
        }

        // Set the driver
        if (!isset($params['driver'])) {
            $params['driver'] = Database::DRIVER_MYSQL;
        }

        if (isset($this->defaults[$params['driver']])) {
            $params += $this->defaults[$params['driver']];
        }

        // Remove empty elements
        return array_filter(
            $params,
            fn ($value) => $value !== null && $value !== '' && $value !== false
        );
    }
}
