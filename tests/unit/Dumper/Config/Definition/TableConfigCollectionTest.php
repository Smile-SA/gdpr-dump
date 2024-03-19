<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Config\Definition;

use Smile\GdprDump\Dumper\Config\Definition\TableConfig;
use Smile\GdprDump\Dumper\Config\Definition\TableConfigCollection;
use Smile\GdprDump\Tests\Unit\TestCase;
use UnexpectedValueException;

class TableConfigCollectionTest extends TestCase
{
    /**
     * Test that an empty collection behaves as expected.
     */
    public function testEmptyCollection(): void
    {
        $collection = new TableConfigCollection();
        $this->assertCount(0, $collection->all());
        $this->assertFalse($collection->has('table1'));
    }

    /**
     * Test adding items to the collection.
     */
    public function testAddItems(): void
    {
        $collection = new TableConfigCollection();
        $collection->add(new TableConfig('table1', []));
        $collection->add(new TableConfig('table2', []));

        $this->assertCount(2, $collection->all());
        $this->assertTrue($collection->has('table1'));
        $this->assertTrue($collection->has('table2'));
        $this->assertInstanceOf(TableConfig::class, $collection->get('table1'));
        $this->assertSame('table1', $collection->get('table1')->getName());
    }

    /**
     * Assert that an exception is thrown when fetching a table that is not defined.
     */
    public function testUndefinedTableName(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $collection = new TableConfigCollection();
        $collection->get('undefined');
    }
}
