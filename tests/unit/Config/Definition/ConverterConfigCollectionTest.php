<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Definition;

use Smile\GdprDump\Dumper\Config\Definition\ConverterConfig;
use Smile\GdprDump\Dumper\Config\Definition\ConverterConfigCollection;
use Smile\GdprDump\Tests\Unit\TestCase;
use UnexpectedValueException;

final class ConverterConfigCollectionTest extends TestCase
{
    // TODO
    // /**
    //  * Test that the collection supports ConverterConfig objects.
    //  */
    // public function testSupportsTableConfig(): void
    // {
    //     $collection = new ConverterConfigCollection();
    //     $collection->add('column1', new ConverterConfig(['converter' => 'converter1']));
    //     $collection->add('column2', new ConverterConfig(['converter' => 'converter2']));

    //     $this->assertCount(2, $collection->all());
    //     $this->assertTrue($collection->has('column1'));
    //     $this->assertTrue($collection->has('column2'));
    //     $this->assertFalse($collection->has('column3'));
    //     $this->assertSame('converter1', $collection->get('column1')->getName());
    //     $this->assertSame('converter2', $collection->get('column2')->getName());
    // }

    // /**
    //  * Assert that an exception is thrown when fetching a table config that is not defined.
    //  */
    // public function testUndefinedTableConfig(): void
    // {
    //     $this->expectException(UnexpectedValueException::class);
    //     $collection = new ConverterConfigCollection();
    //     $collection->get('undefined');
    // }
}
