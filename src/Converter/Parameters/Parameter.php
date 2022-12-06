<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Parameters;

class Parameter
{
    public const TYPE_BOOL = 'bool';
    public const TYPE_STRING = 'string';
    public const TYPE_INT = 'int';
    public const TYPE_FLOAT = 'float';
    public const TYPE_ARRAY = 'array';

    public function __construct(
        private string $name,
        private string $type,
        private bool $required = false,
        private mixed $default = null
    ) {
    }

    /**
     * Get the parameter name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the parameter type.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Check whether the parameter is required.
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * Get the default value.
     */
    public function getDefault(): mixed
    {
        return $this->default;
    }

    /**
     * Check if the parameter is expected to be a scalar value.
     */
    public function isScalar(): bool
    {
        return in_array($this->type, [self::TYPE_BOOL, self::TYPE_STRING, self::TYPE_INT, self::TYPE_FLOAT], true);
    }

    /**
     * Check if the parameter is expected to be an array.
     */
    public function isArray(): bool
    {
        return $this->type === self::TYPE_ARRAY;
    }

    /**
     * Check if the parameter is expected to be an object.
     */
    public function isObject(): bool
    {
        return !$this->isScalar() && !$this->isArray();
    }
}
