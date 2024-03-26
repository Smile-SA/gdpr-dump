<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Config\Definition;

use Smile\GdprDump\Dumper\Config\Definition\TableConfig;
use Smile\GdprDump\Dumper\Config\Validation\ValidationException;
use Smile\GdprDump\Tests\Unit\TestCase;
use UnexpectedValueException;

class TableConfigTest extends TestCase
{
    /**
     * Test the creation of a table filter with empty data.
     */
    public function testEmptyData(): void
    {
        $config = new TableConfig('table1', []);

        $this->assertSame('table1', $config->getName());
        $this->assertEmpty($config->getConverters());

        $this->assertFalse($config->hasLimit());
        $this->assertFalse($config->hasSortOrder());

        $this->assertNull($config->getLimit());
        $this->assertEmpty($config->getSortOrders());
    }

    /**
     * Test the "truncate" parameter.
     */
    public function testTruncateData(): void
    {
        $config = new TableConfig('table1', ['truncate' => true]);

        $this->assertTrue($config->hasLimit());
        $this->assertSame(0, $config->getLimit());
    }

    /**
     * Test the "converters" parameter.
     */
    public function testConverters(): void
    {
        $config = new TableConfig('table1', [
            'converters' => [
                'column1' => ['converter' => 'converterName'],
                'column2' => ['converter' => ''],
                'column3' => ['converter' => '', 'disabled' => true],
                'column4' => [],
            ],
        ]);

        $converters = $config->getConverters();

        // The config must have ignored empty/disabled converters
        $this->assertCount(2, $converters);
    }

    /**
     * Test the condition for skipping data conversion.
     */
    public function testConversionSkipCondition(): void
    {
        $condition = '{{column1}} === null';
        $config = new TableConfig('table1', [
            'skip_conversion_if' => $condition,
        ]);

        $this->assertSame($condition, $config->getSkipCondition());
    }

    /**
     * Test the "where" parameter.
     */
    public function testWhereCondition(): void
    {
        $condition = 'customer_id = 1';
        $config = new TableConfig('table1', [
            'where' => $condition,
        ]);

        $this->assertSame($condition, $config->getWhereCondition());
        $this->assertTrue($config->hasWhereCondition());
    }

    /**
     * Assert that an exception is thrown when a where condition contains disallowed statements.
     */
    public function testWhereConditionWithDisallowedStatement(): void
    {
        $this->expectException(ValidationException::class);
        new TableConfig('table1', [
            'where' => 'drop database example',
        ]);
    }

    /**
     * Assert that an exception is thrown when a where condition is terminated early.
     */
    public function testWhereConditionWithUnmatchedClosingBracket(): void
    {
        $this->expectException(ValidationException::class);
        new TableConfig('table1', [
            'where' => '1); select * from customer where (1',
        ]);
    }

    /**
     * Test the "limit" parameter.
     */
    public function testLimit(): void
    {
        $config = new TableConfig('table1', ['limit' => 100]);

        $this->assertSame(100, $config->getLimit());
        $this->assertTrue($config->hasLimit());

        $config = new TableConfig('table1', ['limit' => null]);
        $this->assertNull($config->getLimit());
        $this->assertFalse($config->hasLimit());
    }

    /**
     * Test the "order_by" parameter.
     */
    public function testSortOrder(): void
    {
        $config = new TableConfig('table1', ['order_by' => 'name, id desc']);

        $this->assertCount(2, $config->getSortOrders());
        $this->assertTrue($config->hasSortOrder());
    }

    /**
     * Assert that an exception is thrown when the sort order is invalid.
     */
    public function testInvalidSortOrder(): void
    {
        $this->expectException(UnexpectedValueException::class);
        new TableConfig('table1', ['order_by' => 'this is not a valid sort order']);
    }
}
