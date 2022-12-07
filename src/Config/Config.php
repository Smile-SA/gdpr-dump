<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config;

class Config implements ConfigInterface
{
    public function __construct(private array $items = [])
    {
    }

    /**
     * @inheritdoc
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->has($key) ? $this->items[$key] : $default;
    }

    /**
     * @inheritdoc
     */
    public function set(string $key, mixed $value): self
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
    public function reset(array $items = []): self
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function merge(array $data): self
    {
        $this->items = $this->mergeArray($this->items, $data);

        return $this;
    }

    /**
     * Merge two arrays.
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
