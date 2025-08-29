<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Util;

use Smile\GdprDump\Tests\Unit\TestCase;
use Smile\GdprDump\Util\Objects;
use stdClass;

final class ObjectsTest extends TestCase
{
    /**
     * Test the "toArray" method.
     */
    public function testToArray(): void
    {
        $object = new stdClass();
        $object->key1 = new stdClass();
        $object->key1->subkey1 = 1;
        $object->key1->subkey2 = 'val';
        $object->key1->subkey3 = [0, 1];

        $this->assertSame(['key1' => (array) $object->key1], Objects::toArray($object));
    }

    /**
     * Test the "merge" method.
     */
    public function testMerge(): void
    {
        $object = new stdClass();
        $override = new stdClass();

        $object->strict_schema = true;
        $object->version = '1.0.0';
        $object->tables_blacklist = ['table1'];
        $object->tables = new stdClass();
        $object->tables->table2 = new stdClass();
        $object->tables->table2->truncate = false;
        $object->tables->table2->where = '1=1';
        $object->tables->table3 = new stdClass();
        $object->tables->table3->truncate = true;
        $object->tables->table4 = new stdClass();

        $override->version = null;
        $override->tables_whitelist = ['table1', 'table2'];
        $override->tables_blacklist = ['table3'];
        $override->tables = new stdClass();
        $override->tables->table1 = new stdClass();
        $override->tables->table1->limit = 10;
        $override->tables->table2 = new stdClass();
        $override->tables->table2->truncate = true;
        $override->tables->table4 = null;

        Objects::merge($object, $override);

        // Assert that objet properties were merged
        $this->assertObjectHasProperty('strict_schema', $object);
        $this->assertObjectHasProperty('version', $object);
        $this->assertObjectHasProperty('tables_blacklist', $object);
        $this->assertObjectHasProperty('tables_whitelist', $object);
        $this->assertObjectHasProperty('tables', $object);
        $this->assertObjectHasProperty('table1', $object->tables);
        $this->assertObjectHasProperty('limit', $object->tables->table1);
        $this->assertObjectHasProperty('table2', $object->tables);
        $this->assertObjectHasProperty('truncate', $object->tables->table2);
        $this->assertObjectHasProperty('where', $object->tables->table2);
        $this->assertObjectHasProperty('table3', $object->tables);
        $this->assertObjectHasProperty('truncate', $object->tables->table3);

        // Assert that a property is removed if its value is an object and is then set to null in the object to merge
        $this->assertObjectNotHasProperty('table4', $object);

        // Assert that values not present in $object2 were kept as-is
        $this->assertSame('1=1', $object->tables->table2->where);
        $this->assertTrue($object->tables->table3->truncate);

        // Assert that non-existing values were added
        $this->assertSame(['table1', 'table2'], $object->tables_whitelist);
        $this->assertSame(10, $object->tables->table1->limit);

        // Assert that non-object values were replaced (including arrays)
        $this->assertNull($object->version);
        $this->assertSame(['table3'], $object->tables_blacklist);
        $this->assertTrue($object->tables->table2->truncate);
    }
}
