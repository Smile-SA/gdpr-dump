<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Parameters;

class InputParameters
{
    public function __construct(private array $values)
    {
    }

    /**
     * Get a parameter value.
     */
    public function get(string $name): mixed
    {
        return $this->values[$name] ?? null;
    }

    /**
     * Check if the parameter value is defined.
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->values);
    }
}
