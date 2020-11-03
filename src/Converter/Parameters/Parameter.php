<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Parameters;

class Parameter
{
    const TYPE_BOOL = 'bool';
    const TYPE_STRING = 'string';
    const TYPE_INT = 'int';
    const TYPE_FLOAT = 'float';
    const TYPE_ARRAY = 'array';

    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var bool
     */
    private $required;

    /**
     * @var mixed
     */
    private $default;

    /**
     * @param string $name
     * @param string|null $type
     * @param bool $required
     * @param mixed $default
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(string $name, string $type = null, bool $required = false, $default = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->required = $required;
        $this->default = $default;
    }

    /**
     * Get the parameter name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the parameter type.
     *
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Check whether the parameter is required.
     *
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * Get the default value.
     *
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Check if the parameter is expected to be a scalar value.
     *
     * @return bool
     */
    public function isScalar(): bool
    {
        return $this->type !== null
            && in_array($this->type, [self::TYPE_BOOL, self::TYPE_STRING, self::TYPE_INT, self::TYPE_FLOAT], true);
    }

    /**
     * Check if the parameter is expected to be an array.
     *
     * @return bool
     */
    public function isArray(): bool
    {
        return $this->type === self::TYPE_ARRAY;
    }

    /**
     * Check if the parameter is expected to be an object.
     *
     * @return bool
     */
    public function isObject(): bool
    {
        return $this->type !== null && !$this->isScalar() && !$this->isArray();
    }
}
