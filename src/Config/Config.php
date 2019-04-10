<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Config;

class Config implements ConfigInterface
{
    /**
     * @var array
     */
    private $items = [];

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->items = $data;
    }

    /**
     * @inheritdoc
     */
    public function get(string $key, $default = null)
    {
        return $this->findByPath($key, $default);
    }

    /**
     * @inheritdoc
     */
    public function has($key): bool
    {
        $time = microtime(true);

        return $this->get($key, $time) !== $time;
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value): ConfigInterface
    {
        $this->items[$key] = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function merge(array $data): ConfigInterface
    {
        $this->items = $this->mergeArray($this->items, $data);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * Find a config item by path.
     *
     * @param string $path
     * @param mixed $default
     * @return mixed
     */
    private function findByPath(string $path, $default = null)
    {
        $result = $this->items;

        foreach (explode('.', $path) as $key) {
            if (!isset($result[$key])) {
                $result = $default;
                break;
            }

            $result = $result[$key];
        }

        return $result;
    }

    /**
     * Merge two arrays.
     * We don't use array_merge_recursive because it doesn't override string keys
     *
     * @param array $data
     * @param array $override
     * @return array
     */
    private function mergeArray(array $data, array $override)
    {
        foreach ($override as $key => $value) {
            if (array_key_exists($key, $data)) {
                if (is_numeric($key)) {
                    // Key is numeric, append value
                    $data[] = $value;
                } elseif (is_array($value) && is_array($data[$key])) {
                    // Key is associative, merge and overwrite value
                    $data[$key] = $this->mergeArray($data[$key], $value);
                } else {
                    // Key is associative, overwrite value
                    $data[$key] = $value;
                }
            } else {
                // Value not present in result array, append it
                $data[$key] = $value;
            }
        }

        return $data;
    }
}
