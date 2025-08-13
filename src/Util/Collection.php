<?php

declare(strict_types=1);

namespace Smile\GdprDump\Util;

use ArrayIterator;
use IteratorAggregate;
use Traversable;
use UnexpectedValueException;

/**
 * @template T
 * @implements IteratorAggregate<string, T>
 */
abstract class Collection implements IteratorAggregate
{
    protected string $descriptor = 'item';

    /**
     * @param array<string, T> $items
     */
    public function __construct(private array $items = [])
    {
        $this->items = $items;
    }

    /**+
     * Add an item to the collection.
     *
     * @param T $item
     */
    public function add(string $index, mixed $item): static
    {
        $this->items[$index] = $item;

        return $this;
    }

    /**
     * Get an item from the collection.
     *
     * @return T
     * @throws UnexpectedValueException
     */
    public function get(string $index): mixed
    {
        return $this->has($index)
            ? $this->items[$index]
            : throw new UnexpectedValueException(sprintf('The %s "%s" is not defined.', $this->descriptor, $index));
    }

    /**
     * Check whether an item exists.
     */
    public function has(string $index): bool
    {
        return array_key_exists($index, $this->items);
    }

    /**
     * Remove an item from the collection.
     */
    public function remove(string $index): bool
    {
        if ($this->has($index)) {
            unset($this->items[$index]);
            return true;
        }

        return false;
    }

    /**
     * Get all items.
     *
     * @return array<string, T>
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * @return ArrayIterator<string, T>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
}
