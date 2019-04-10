<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Config;

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
    public function has($key): bool;

    /**
     * Set a config item.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function set($key, $value): ConfigInterface;

    /**
     * Merge the config data with another set of items.
     *
     * @param array $data
     * @return $this
     */
    public function merge(array $data): ConfigInterface;

    /**
     * Get the configuration data.
     *
     * @return array
     */
    public function toArray(): array;
}
