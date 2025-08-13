<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Config\Definition;

use Smile\GdprDump\Tests\Unit\TestCase;
use Smile\GdprDump\Util\Collection;
use UnexpectedValueException;

// TODO REMOVE OR USE THE COLLECTION OBJECT
final class CollectionTest extends TestCase
{
    /**
     * Test the collection object
     */
    public function testCollection(): void
    {
        $collection = $this->createCollection([
            'index1' => (object) ['name' => 'item1'],
            'index2' => (object) ['name' => 'item2'],
        ]);

        $this->assertCount(2, $collection->all());
        $this->assertTrue($collection->has('index1'));
        $this->assertTrue($collection->has('index2'));
        $this->assertFalse($collection->has('index3'));

        $this->assertObjectHasProperty('name', $collection->get('index1'));
        $this->assertSame('item1', $collection->get('index1')->name);

        $this->assertObjectHasProperty('name', $collection->get('index2'));
        $this->assertSame('item2', $collection->get('index2')->name);

        $removed = $collection->remove('index1');
        $this->assertCount(1, $collection->all());
        $this->assertTrue($removed);
        $this->assertFalse($collection->has('index1'));

        $removed = $collection->remove('index1');
        $this->assertCount(1, $collection->all());
        $this->assertFalse($removed);
    }

    /**
     * Test that an empty collection behaves as expected.
     */
    public function testEmptyCollection(): void
    {
        $collection = $this->createCollection();
        $this->assertCount(0, $collection->all());
        $this->assertFalse($collection->has('0'));
    }

    /**
     * Test adding items to the collection.
     */
    public function testAddItems(): void
    {
        $collection = $this->createCollection();

        $collection->add('index1', (object) ['name' => 'item1']);
        $collection->add('index2', (object) ['name' => 'item2']);

        $this->assertCount(2, $collection->all());
        $this->assertTrue($collection->has('index1'));
        $this->assertTrue($collection->has('index2'));

        $this->assertObjectHasProperty('name', $collection->get('index1'));
        $this->assertSame('item1', $collection->get('index1')->name);

        $this->assertObjectHasProperty('name', $collection->get('index2'));
        $this->assertSame('item2', $collection->get('index2')->name);
    }

    /**
     * Assert that an exception is thrown when fetching an item that is not defined.
     */
    public function testUndefinedItem(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $collection = $this->createCollection();
        $collection->get('undefined');
    }

    /**
     * Create a generic collection.
     *
     * @param array<string, object> $items
     * @return Collection<object>
     */
    private function createCollection(array $items = []): object
    {
        $class = new class extends Collection {
        };

        return new $class($items);
    }
}
