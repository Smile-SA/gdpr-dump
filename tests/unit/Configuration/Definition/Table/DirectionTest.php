<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Configuration\Definition\Table;

use Smile\GdprDump\Configuration\Definition\Table\Direction;
use Smile\GdprDump\Tests\Unit\TestCase;

final class DirectionTest extends TestCase
{
    /**
     * Test the direction values.
     */
    public function testSortOrder(): void
    {
        $this->assertSame('ASC', Direction::ASC->toString());
        $this->assertSame('DESC', Direction::DESC->toString());
    }
}
