<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Config\Table;

use RuntimeException;
use Smile\GdprDump\Dumper\Config\Table\Filter\Filter;
use Smile\GdprDump\Dumper\Config\Table\TableConfig;
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
        $this->assertFalse($config->hasFilter());
        $this->assertFalse($config->hasSortOrder());

        $this->assertNull($config->getLimit());
        $this->assertEmpty($config->getFilters());
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

        $this->assertStringContainsString('$context[\'row_data\'][\'column1\']', $config->getSkipCondition());
    }

    /**
     * Test the "filter" parameter.
     */
    public function testFilter(): void
    {
        $config = new TableConfig('table1', [
            'filters' => [
                ['column1', Filter::OPERATOR_IS_NULL],
                ['column2', Filter::OPERATOR_EQ, 'value'],
            ],
        ]);

        $this->assertCount(2, $config->getFilters());
        $this->assertTrue($config->hasFilter());
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

    /**
     * Assert that an exception is thrown when condition for skipping data conversion is invalid.
     */
    public function testInvalidCondition(): void
    {
        $this->expectException(RuntimeException::class);
        new TableConfig('table1', ['skip_conversion_if' => 'sleep(100)']);
    }
}
