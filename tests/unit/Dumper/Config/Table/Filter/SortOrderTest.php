<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Config\Table\Filter;

use Smile\GdprDump\Dumper\Config\Table\Filter\SortOrder;
use Smile\GdprDump\Tests\Unit\TestCase;

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
     *
     * @expectedException \UnexpectedValueException
     */
    public function testInvalidDirection()
    {
        new SortOrder('column1', 'not_exists');
    }
}
