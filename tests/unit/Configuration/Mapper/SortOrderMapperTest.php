<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Configuration\Mapper;

use Smile\GdprDump\Configuration\Definition\Table\Direction;
use Smile\GdprDump\Configuration\Definition\Table\SortOrder;
use Smile\GdprDump\Configuration\Exception\UnexpectedValueException;
use Smile\GdprDump\Configuration\Mapper\SortOrderMapper;
use Smile\GdprDump\Tests\Unit\TestCase;

final class SortOrderMapperTest extends TestCase
{
    /**
     * Test the creation of SortOrder objects.
     */
    public function testBuilder(): void
    {
        $sortOrders = $this->createSortOrder('name desc, description, id asc');

        $this->assertCount(3, $sortOrders);
        $this->assertSame([0, 1, 2], array_keys($sortOrders));

        $this->assertInstanceOf(SortOrder::class, $sortOrders[0]);
        $this->assertSame('name', $sortOrders[0]->getColumn());
        $this->assertSame(Direction::DESC, $sortOrders[0]->getDirection());

        $this->assertInstanceOf(SortOrder::class, $sortOrders[1]);
        $this->assertSame('description', $sortOrders[1]->getColumn());
        $this->assertSame(Direction::ASC, $sortOrders[1]->getDirection());

        $this->assertInstanceOf(SortOrder::class, $sortOrders[2]);
        $this->assertSame('id', $sortOrders[2]->getColumn());
        $this->assertSame(Direction::ASC, $sortOrders[2]->getDirection());

        // Empty value is converted to an empty array
        $sortOrders = $this->createSortOrder('');
        $this->assertSame([], $sortOrders);
    }

    /**
     * Assert that an exception is thrown when only the order separator (comma) was provided.
     */
    public function testOnlySeparatorProvided(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->createSortOrder(',');
    }

    /**
     * Assert that an exception is thrown when one of the sort orders is empty
     */
    public function testEmptyOrderProvided(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->createSortOrder('id asc,');
    }

    /**
     * Assert that an exception is thrown when a sort order is invalid.
     */
    public function testTooManyParts(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->createSortOrder('id asc id');
    }

    /**
     * Assert that an exception is thrown when a sort order direction is invalid.
     */
    public function testInvalidSortDirection(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->createSortOrder('id not_exists');
    }

    /**
     * Create a SortOrder object from the provided string.
     */
    private function createSortOrder(string $orderBy): array
    {
        $mapper = new SortOrderMapper();

        return $mapper->fromString($orderBy);
    }
}
