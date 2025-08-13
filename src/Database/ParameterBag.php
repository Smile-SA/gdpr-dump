<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database;

use Smile\GdprDump\Database\Driver\DatabaseDriver;
use UnexpectedValueException;

final class ParameterBag
{
    private array $params;
    private array $defaults = [
        DatabaseDriver::MYSQL => ['host' => 'localhost', 'user' => 'root'],
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
        // Set the driver
        if (!isset($params['driver'])) {
            $params['driver'] = DatabaseDriver::DEFAULT;
        }

        if (isset($this->defaults[$params['driver']])) {
            $params += $this->defaults[$params['driver']];
        }

        // Remove empty elements
        return array_filter(
            $params,
            fn (mixed $value): bool => $value !== null && $value !== ''
        );
    }
}
