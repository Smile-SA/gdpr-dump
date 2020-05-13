<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Config\Table\Filter;

use Smile\GdprDump\Dumper\Config\Table\Filter\SortOrder;
use Smile\GdprDump\Tests\Unit\TestCase;
use UnexpectedValueException;

class SortOrderTest extends TestCase
{
    /**
     * Test the creation of a sort order.
     */
    public function testSortOrder()
    {
        $sortOrder = new SortOrder('column1');
        $this->assertSame('column1', $sortOrder->getColumn());
        $this->assertSame(SortOrder::DIRECTION_ASC, $sortOrder->getDirection());

        $sortOrder = new SortOrder('column1', SortOrder::DIRECTION_DESC);
        $this->assertSame(SortOrder::DIRECTION_DESC, $sortOrder->getDirection());
    }

    /**
     * Assert that an exception is thrown when the direction is invalid.
     */
    public function testInvalidDirection()
    {
        $this->expectException(UnexpectedValueException::class);
        new SortOrder('column1', 'not_exists');
    }
}
