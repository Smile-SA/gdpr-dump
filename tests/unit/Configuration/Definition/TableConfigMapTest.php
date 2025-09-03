<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Configuration\Definition;

use Smile\GdprDump\Configuration\Definition\Table\SortOrder;
use Smile\GdprDump\Configuration\Definition\TableConfig;
use Smile\GdprDump\Configuration\Definition\TableConfigMap;
use Smile\GdprDump\Tests\Unit\TestCase;

final class TableConfigMapTest extends TestCase
{
    /**
     * Test the map filters.
     */
    public function testFilters(): void
    {
        $map = new TableConfigMap([
            'table1' => (new TableConfig())->setLimit(0),
            'table2' => (new TableConfig())->setLimit(10),
            'table3' => (new TableConfig())->setWhere('1=1'),
            'table4' => (new TableConfig())->setSortOrders([new SortOrder('id')]),
            'table5' => new TableConfig(),
        ]);

        $this->assertSame(['table1', 'table2', 'table3'], $map->getTablesToFilter());
        $this->assertSame(['table4'], $map->getTablesToSort());
    }

    /**
     * Assert that the map items are cloned.
     */
    public function testDeepClone(): void
    {
        $map = new TableConfigMap([
            'table1' => new TableConfig(),
            'table2' => new TableConfig(),
        ]);

        $clonedMap = clone $map;
        $this->assertNotSame($map->toArray(), $clonedMap->toArray());
    }
}
