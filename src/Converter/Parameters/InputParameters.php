<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Parameters;

class InputParameters
{
    /**
     * @var array
     */
    private array $values;

    /**
     * @param array $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * Get a parameter value.
     *
     * @param string $name
     * @return mixed
     */
    public function get(string $name)
    {
        return $this->values[$name] ?? null;
    }

    /**
     * Check if the parameter value is defined.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->values);
    }
}
