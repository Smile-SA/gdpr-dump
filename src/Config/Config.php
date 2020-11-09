<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config;

use Smile\GdprDump\Config\Compiler\Compiler;
use Smile\GdprDump\Config\Compiler\Processor\EnvVarProcessor;
use Smile\GdprDump\Config\Compiler\Processor\VersionProcessor;

class Config implements ConfigInterface
{
    /**
     * @var array
     */
    private $items;

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
        return $this->has($key) ? $this->items[$key] : $default;
    }

    /**
     * @inheritdoc
     */
    public function set(string $key, $value): ConfigInterface
    {
        $this->items[$key] = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * @inheritdoc
     */
    public function reset(array $items = []): ConfigInterface
    {
        $this->items = $items;

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
    public function compile(): void
    {
        $processors = [
            new EnvVarProcessor(),
            new VersionProcessor(),
        ];

        $compiler = new Compiler($processors);
        $compiler->compile($this);
    }

    /**
     * Merge two arrays.
     *
     * @param array $data
     * @param array $override
     * @return array
     */
    private function mergeArray(array $data, array $override): array
    {
        foreach ($override as $key => $value) {
            if (array_key_exists($key, $data)) {
                if (is_array($value) && is_array($data[$key])) {
                    // Merge values
                    $data[$key] = $this->mergeArray($data[$key], $value);

                    // If a key of the array was unset and the array is empty as a result, unset it
                    // This is necessary because JSON schema validation does not allow empty array as object values
                    if (!empty($value) && empty($data[$key])) {
                        unset($data[$key]);
                    }
                } elseif ($value === null && is_array($data[$key])) {
                    // Remove array key (allows to remove an existing config item by setting it to null)
                    unset($data[$key]);
                } else {
                    // Overwrite value
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
