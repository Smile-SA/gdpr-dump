<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config;

interface ConfigInterface
{
    /**
     * Get a config value.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Set a config item.
     */
    public function set(string $key, mixed $value): self;

    /**
     * Check whether a key is defined in the config.
     */
    public function has(string $key): bool;

    /**
     * Get the configuration data.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;

    /**
     * Reset the config items.
     *
     * @param array<string, mixed> $items
     */
    public function reset(array $items): self;

    /**
     * Merge the config data with another set of items.
     *
     * @param array<string, mixed> $data
     */
    public function merge(array $data): self;
}
