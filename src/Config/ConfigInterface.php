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
     * Check whether a key is defined in the config.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Set a config item.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function set(string $key, $value): ConfigInterface;

    /**
     * Get the configuration data.
     *
     * @return array
     */
    public function toArray(): array;

    /**
     * Merge the config data with another set of items.
     *
     * @param array $data
     * @return $this
     */
    public function merge(array $data): ConfigInterface;
}
