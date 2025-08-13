<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Resource;

use Smile\GdprDump\Config\Exception\ConfigLoadException;
use Smile\GdprDump\Config\Resource\Resource;

/**
 * Resource stack (resources are parsed from most recently added to least recently, then merged in reverse order).
 */
class ResourceStack
{
    /**
     * @var Resource[]
     */
    protected array $items = [];

    /**
     * Push a resource to the stack.
     */
    public function push(Resource $resource): void
    {
        $this->items[] = $resource;
    }

    /**
     * Pop a resource from the stack.
     *
     * @throws ConfigLoadException
     */
    public function pop(): Resource
    {
        if ($this->isEmpty()) {
            throw new ConfigLoadException('The resource stack is empty.');
        }

        return array_pop($this->items);
    }

    /**
     * Check whether the stack is empty.
     */
    public function isEmpty(): bool
    {
        return !$this->items;
    }

    /**
     * Clear the stack.
     */
    public function clear(): void
    {
        $this->items = [];
    }
}
