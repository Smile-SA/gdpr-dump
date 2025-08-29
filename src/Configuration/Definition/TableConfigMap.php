<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Definition;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @extends ArrayCollection<string, TableConfig>
 */
final class TableConfigMap extends ArrayCollection
{
    /**
     * @return string[]
     */
    public function getTablesToFilter(): array
    {
        $filter = fn (TableConfig $tableConfig) => $tableConfig->getWhere() !== '' || $tableConfig->getLimit() > 0;

        return $this->filter($filter)->getKeys();
    }

    /**
     * @return string[]
     */
    public function getTablesToSort(): array
    {
        $filter = fn (TableConfig $tableConfig) => (bool) $tableConfig->getSortOrders();

        return $this->filter($filter)->getKeys();
    }

    /**
     * @return string[]
     */
    public function getTablesToTruncate(): array
    {
        $filter = fn (TableConfig $tableConfig) => (bool) $tableConfig->isTruncate();

        return $this->filter($filter)->getKeys();
    }

    /**
     * Deep clone the object.
     */
    public function __clone(): void
    {
        foreach ($this as $index => $tableConfig) {
            $this->set($index, clone $tableConfig);
        }
    }
}
