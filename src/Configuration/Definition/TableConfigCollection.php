<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Definition;

use Smile\GdprDump\Util\Collection;

// TODO
final class TableConfigCollection extends Collection
{
    protected string $descriptor = 'table';

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
}
