<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Configuration\Definition\Table;

use Smile\GdprDump\Configuration\Definition\Table\Direction;
use Smile\GdprDump\Configuration\Definition\Table\SortOrder;
use Smile\GdprDump\Tests\Unit\TestCase;

final class SortOrderTest extends TestCase
{
    /**
     * Test the creation of a sort order.
     */
    public function testSortOrder(): void
    {
        $sortOrder = new SortOrder('column1');
        $this->assertSame('column1', $sortOrder->getColumn());
        $this->assertSame(Direction::ASC, $sortOrder->getDirection());

        $sortOrder = new SortOrder('column1', Direction::DESC);
        $this->assertSame(Direction::DESC, $sortOrder->getDirection());
    }
}
