<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Definition;

use Smile\GdprDump\Dumper\Config\Definition\TableConfig;
use Smile\GdprDump\Dumper\Config\Definition\TableConfigCollection;
use Smile\GdprDump\Tests\Unit\TestCase;
use UnexpectedValueException;

final class TableConfigCollectionTest extends TestCase
{
    // TODO
    // /**
    //  * Test that the collection supports TableConfig objects.
    //  */
    // public function testSupportsTableConfig(): void
    // {
    //     $collection = new TableConfigCollection();
    //     $collection->add('table1', new TableConfig('table1', []));
    //     $collection->add('table2', new TableConfig('table2', []));

    //     $this->assertCount(2, $collection->all());
    //     $this->assertTrue($collection->has('table1'));
    //     $this->assertTrue($collection->has('table2'));
    //     $this->assertFalse($collection->has('table3'));
    //     $this->assertSame('table1', $collection->get('table1')->getName());
    //     $this->assertSame('table2', $collection->get('table2')->getName());
    // }

    // /**
    //  * Assert that an exception is thrown when fetching a table config that is not defined.
    //  */
    // public function testUndefinedTableConfig(): void
    // {
    //     $this->expectException(UnexpectedValueException::class);
    //     $collection = new TableConfigCollection();
    //     $collection->get('undefined');
    // }
}
