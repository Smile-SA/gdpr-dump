<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Loader;

use Smile\GdprDump\Util\Objects;
use stdClass;

/**
 * Contains the configuration parsed by the loader.
 */
final class Container
{
    private stdClass $configuration;

    public function __construct(?stdClass $items = null)
    {
        $this->configuration = $items ? Objects::deepClone($items) : new stdClass();
    }

    /**
     * Get a configuration item by property name.
     */
    public function get(string $property): mixed
    {
        return $this->configuration->{$property} ?? null;
    }

    /**
     * Set a configuration item.
     */
    public function set(string $property, mixed $value): self
    {
        $this->configuration->{$property} = $value;

        return $this;
    }

    /**
     * Check whether the configuration has the specified property.
     */
    public function has(string $property): bool
    {
        return property_exists($this->configuration, $property);
    }

    /**
     * Remove an item by property name.
     */
    public function remove(string $property): self
    {
        unset($this->configuration->{$property});

        return $this;
    }

    /**
     * Get the configuration object.
     */
    public function getRoot(): stdClass
    {
        return $this->configuration;
    }

    /**
     * Get the parsed configuration as array.
     */
    public function toArray(): array
    {
        return Objects::toArray($this->configuration);
    }
}
