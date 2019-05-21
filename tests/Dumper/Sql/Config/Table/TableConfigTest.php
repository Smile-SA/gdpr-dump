<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Tests\Dumper\Sql\Config\Table\Filter;

use PHPUnit\Framework\TestCase;
use Smile\Anonymizer\Dumper\Sql\Config\Table\Filter\Filter;
use Smile\Anonymizer\Dumper\Sql\Config\Table\TableConfig;

class TableConfigTest extends TestCase
{
    /**
     * Test the creation of a table filter with empty data.
     */
    public function testEmptyData()
    {
        $config = new TableConfig('table1', []);

        $this->assertSame('table1', $config->getName());
        $this->assertEmpty($config->getConverters());

        $this->assertTrue($config->isSchemaDumped());
        $this->assertTrue($config->isDataDumped());

        $this->assertFalse($config->hasLimit());
        $this->assertFalse($config->hasFilter());
        $this->assertFalse($config->hasSortOrder());

        $this->assertNull($config->getLimit());
        $this->assertEmpty($config->getFilters());
        $this->assertEmpty($config->getSortOrders());
    }

    /**
     * Test the "ignore" parameter.
     */
    public function testIgnoreSchema()
    {
        $config = new TableConfig('table1', ['ignore' => true]);

        $this->assertFalse($config->isSchemaDumped());
    }

    /**
     * Test the "truncate" parameter.
     */
    public function testIgnoreData()
    {
        $config = new TableConfig('table1', ['truncate' => true]);

        $this->assertFalse($config->isDataDumped());
    }

    /**
     * Test the "converters" parameter.
     */
    public function testConverters()
    {
        $config = new TableConfig('table1', [
            'converters' => [
                'column1' => 'converterName',
                'column2' => ['converter' => 'converterName'],
                'column3' => '', // should not be included because no converter name was specified
                'column4' => [], // should not be included because no converter name was specified
                'column5' => ['converter' => ''], // should not be included because no converter name was specified
            ],
        ]);

        $converters = $config->getConverters();

        $this->assertCount(2, $converters);
        $this->assertArrayHasKey('column1', $converters);
        $this->assertArrayHasKey('column2', $converters);
        $this->assertArrayNotHasKey('column3', $converters);
        $this->assertArrayNotHasKey('column4', $converters);
    }

    /**
     * Test the "filter" parameter.
     */
    public function testFilter()
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
    public function testLimit()
    {
        $config = new TableConfig('table1', ['limit' => 100]);

        $this->assertSame(100, $config->getLimit());
        $this->assertTrue($config->hasLimit());

        $config = new TableConfig('table1', ['limit' => null]);
        $this->assertNull($config->getLimit());
        $this->assertFalse($config->hasLimit());
    }

    /**
     * Test the "orderBy" parameter.
     */
    public function testSortOrder()
    {
        $config = new TableConfig('table1', ['orderBy' => 'name, id desc']);

        $this->assertCount(2, $config->getSortOrders());
        $this->assertTrue($config->hasSortOrder());
    }

    /**
     * Test if an exception is thrown when the "ignore" property is combined with another property.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testIgnoreSchemaConflict()
    {
        new TableConfig('table1', ['ignore' => true, 'limit' => 100]);
    }

    /**
     * Test if an exception is thrown when the "ignore" property is combined with another property.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testIgnoreDataConflict()
    {
        $config = new TableConfig('table1', ['truncate' => true, 'limit' => 100]);

        var_dump($config->isDataDumped());
        var_dump($config->getLimit());
    }
}
