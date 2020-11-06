<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config;

interface ConfigInterface
{
    /**
     * Get a config value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * Set a config item.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function set(string $key, $value): ConfigInterface;

    /**
     * Check whether a key is defined in the config.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Get the configuration data.
     *
     * @return array
     */
    public function toArray(): array;

    /**
     * Reset the config items.
     *
     * @param array $items
     * @return $this
     */
    public function reset(array $items): ConfigInterface;

    /**
     * Merge the config data with another set of items.
     *
     * @param array $data
     * @return $this
     */
    public function merge(array $data): ConfigInterface;

    /**
     * Compile the configuration.
     */
    public function compile(): void;
}
