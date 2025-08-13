<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Loader;

interface ContainerInterface
{
    // TODO
    /**
     * Get a config value.
     */
    //protected function get(string $key, mixed $default = null): mixed;

    /**
     * Set a config item.
     */
    //protected function set(string $key, mixed $value): self;

    /**
     * Check whether a key is defined in the config.
     */
    //protected function has(string $key): bool;

    /**
     * Remove a config item.
     */
    //protected function remove(string $key): self;

    /**
     * Populate the configuration from the provided array.
     */
    public function fromArray(array $items): self;

    /**
     * Return an array representation of the configuration.
     */
    public function toArray(): array;

    /**
     * Reset the container data.
     */
    //public function reset(array $items): self;

    /**
     * Lock the container data.
     */
    public function freeze(): self;

    /**
     * Merge the config data with another set of items.
     */
    //public function merge(array $data): self;
}
