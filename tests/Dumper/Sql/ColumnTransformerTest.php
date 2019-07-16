<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Dumper\Sql;

use Smile\GdprDump\Dumper\Sql\ColumnTransformer;
use Smile\GdprDump\Tests\Converter\TestConverter;
use Smile\GdprDump\Tests\DatabaseTestCase;

class ColumnTransformerTest extends DatabaseTestCase
{
    /**
     * Check if a value is transformed properly.
     */
    public function testTransformValue()
    {
        $transformer = $this->createTransformer();

        $value = $transformer->transform('mytable', 'mycolumn', 'myvalue', []);
        $this->assertSame('test_myvalue', $value);
    }

    /**
     * Test if null values are ignored.
     */
    public function testNullValueNotTransformed()
    {
        $transformer = $this->createTransformer();

        $value = $transformer->transform('mytable', 'mycolumn', null, []);
        $this->assertNull($value);
    }

    /**
     * Test if unknown tables are ignored.
     */
    public function testIgnoredTables()
    {
        $transformer = $this->createTransformer();

        $value = $transformer->transform('mytable', 'unknown_column', 'myvalue', []);
        $this->assertSame('myvalue', $value);
    }

    /**
     * Test if unknown columns are ignored.
     */
    public function testIgnoredColumns()
    {
        $transformer = $this->createTransformer();

        $value = $transformer->transform('unknown_table', 'mycolumn', 'myvalue', []);
        $this->assertSame('myvalue', $value);
    }

    /**
     * Create a column transformer object.
     *
     * @return ColumnTransformer
     */
    private function createTransformer(): ColumnTransformer
    {
        $converters = [
            'mytable' => [
                'mycolumn' => new TestConverter(),
            ],
        ];

        return new ColumnTransformer($converters);
    }
}
