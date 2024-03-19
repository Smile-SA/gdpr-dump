<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Config\Definition;

use ArrayIterator;
use IteratorAggregate;
use Traversable;
use UnexpectedValueException;

/**
 * @implements IteratorAggregate<TableConfig>
 */
class TableConfigCollection implements IteratorAggregate
{
    /**
     * @var TableConfig[]
     */
    private array $items = [];

    /**+
     * Add an item.
     */
    public function add(TableConfig $tableConfig): void
    {
        $this->items[$tableConfig->getName()] = $tableConfig;
    }

    /**
     * Get an item.
     *
     * @throws UnexpectedValueException
     */
    public function get(string $name): TableConfig
    {
        return $this->has($name)
            ? $this->items[$name]
            : throw new UnexpectedValueException(sprintf('The table "%s" is not defined.', $name));
    }

    /**
     * Check whether an item exists.
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->items);
    }

    /**
     * Get all items.
     *
     * @return TableConfig[]
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * @return ArrayIterator<string, TableConfig>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
}
