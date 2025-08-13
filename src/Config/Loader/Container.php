<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Loader;

use Smile\GdprDump\Util\Objects;

class Container implements ContainerInterface
{
    protected array $items = [];
    private bool $frozen = false;

    protected function get(string $key, mixed $default = null): mixed
    {
        return $this->has($key) ? $this->items[$key] : $default;
    }

    protected function set(string $key, mixed $value): self
    {
        if ($this->frozen) {
            throw new \Exception('TODO frozen');
        }

        $this->items[$key] = $value;

        return $this;
    }

    protected function remove(string $key): self
    {
        if ($this->frozen) {
            throw new \Exception('TODO frozen');
        }

        unset($this->items[$key]);

        return $this;
    }

    protected function has(string $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function fromArray(array $items): self
    {
        if ($this->frozen) {
            throw new \Exception('TODO frozen');
        }

        $this->items = $items;

        return $this;
    }

    public function reset(array $items = []): self
    {
        if ($this->frozen) {
            throw new \Exception('TODO frozen');
        }

        $this->items = $items;

        return $this;
    }

    public function freeze(): self
    {
        $this->frozen = true;

        return $this;
    }

    // TODO
    public function merge(array $data): self
    {
        $this->items = Objects::merge($this->items, $data);

        return $this;
    }
}
