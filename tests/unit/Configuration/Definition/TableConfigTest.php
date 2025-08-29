<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Configuration\Definition;

use Smile\GdprDump\Configuration\Definition\ConverterConfig;
use Smile\GdprDump\Configuration\Definition\ConverterConfigMap;
use Smile\GdprDump\Configuration\Definition\Table\SortOrder;
use Smile\GdprDump\Configuration\Definition\TableConfig;
use Smile\GdprDump\Configuration\Exception\InvalidQueryException;
use Smile\GdprDump\Tests\Unit\TestCase;

final class TableConfigTest extends TestCase
{
    /**
     * Test the creation of a table config object.
     */
    public function testObjectCreation(): void
    {
        $truncate = true;
        $where = '1=1';
        $limit = 10;
        $skipCondition = 'true';
        $sortOrders = [new SortOrder('id')];
        $converters = ['username' => new ConverterConfig('randomizeText')];

        $tableConfig = (new TableConfig())
            ->setTruncate($truncate)
            ->setWhere($where)
            ->setLimit($limit)
            ->setSkipCondition($skipCondition)
            ->setSortOrders($sortOrders)
            ->setConverterConfigs(new ConverterConfigMap($converters));

        $this->assertSame($truncate, $tableConfig->isTruncate());
        $this->assertSame($where, $tableConfig->getWhere());
        $this->assertSame($limit, $tableConfig->getLimit());
        $this->assertSame($skipCondition, $tableConfig->getSkipCondition());
        $this->assertSame($sortOrders, $tableConfig->getSortOrders());
        $this->assertSame($converters, $tableConfig->getConverterConfigs()->toArray());
    }

    /**
     * Test the default values.
     */
    public function testDefaultValues(): void
    {
        $tableConfig = new TableConfig();
        $this->assertFalse($tableConfig->isTruncate());
        $this->assertSame('', $tableConfig->getWhere());
        $this->assertNull($tableConfig->getLimit());
        $this->assertSame('', $tableConfig->getSkipCondition());
        $this->assertSame([], $tableConfig->getSortOrders());
        $this->assertTrue($tableConfig->getConverterConfigs()->isEmpty());
    }

    /**
     * Assert that an exception is thrown when a where condition contains disallowed statements.
     */
    public function testWhereConditionWithDisallowedStatement(): void
    {
        $this->expectException(InvalidQueryException::class);
        (new TableConfig())->setWhere('drop database example');
    }

    /**
     * Assert that object properties are cloned.
     */
    public function testDeepClone(): void
    {
        $tableConfig = (new TableConfig())
            ->setSortOrders([new SortOrder('id')])
            ->setConverterConfigs(new ConverterConfigMap(['username' => new ConverterConfig('randomizeText')]));

        $clonedConfig = clone $tableConfig;
        $this->assertNotSame($tableConfig->getConverterConfigs(), $clonedConfig->getConverterConfigs());
        $this->assertNotSame($tableConfig->getSortOrders(), $clonedConfig->getSortOrders());
    }
}
