<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Config\Definition;

use Smile\GdprDump\Config\Definition\ConverterConfig;
use Smile\GdprDump\Config\Definition\Table\SortOrder;
use Smile\GdprDump\Config\Definition\TableConfig;
use Smile\GdprDump\Tests\Unit\TestCase;
use UnexpectedValueException;

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
        $converters = [new ConverterConfig('randomizeText')];

        $config = (new TableConfig())
            ->setTruncate($truncate)
            ->setWhere($where)
            ->setLimit($limit)
            ->setSkipCondition($skipCondition)
            ->setSortOrders($sortOrders)
            ->setConvertersConfig($converters);

        $this->assertSame($truncate, $config->isTruncate());
        $this->assertSame($where, $config->getWhere());
        $this->assertSame($limit, $config->getLimit());
        $this->assertSame($skipCondition, $config->getSkipCondition());
        $this->assertSame($sortOrders, $config->getSortOrders());
        $this->assertSame($converters, $config->getConvertersConfig());
    }

    /**
     * Test the default values.
     */
    public function testDefaultValues(): void
    {
        $config = new TableConfig();
        $this->assertFalse($config->isTruncate());
        $this->assertSame('', $config->getWhere());
        $this->assertNull($config->getLimit());
        $this->assertSame('', $config->getSkipCondition());
        $this->assertSame([], $config->getSortOrders());
        $this->assertSame([], $config->getConvertersConfig());
    }

    /**
     * Assert that an exception is thrown when a where condition contains disallowed statements.
     */
    public function testWhereConditionWithDisallowedStatement(): void
    {
        $this->expectException(UnexpectedValueException::class);
        (new TableConfig())->setWhere('drop database example');
    }
}
