<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Definition;

use Smile\GdprDump\Configuration\Definition\ConverterConfig;
use Smile\GdprDump\Configuration\Definition\Table\SortOrder;
use Smile\GdprDump\Configuration\Definition\TableConfig;
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

        $configuration = (new TableConfig())
            ->setTruncate($truncate)
            ->setWhere($where)
            ->setLimit($limit)
            ->setSkipCondition($skipCondition)
            ->setSortOrders($sortOrders)
            ->setConvertersConfig($converters);

        $this->assertSame($truncate, $configuration->isTruncate());
        $this->assertSame($where, $configuration->getWhere());
        $this->assertSame($limit, $configuration->getLimit());
        $this->assertSame($skipCondition, $configuration->getSkipCondition());
        $this->assertSame($sortOrders, $configuration->getSortOrders());
        $this->assertSame($converters, $configuration->getConvertersConfig());
    }

    /**
     * Test the default values.
     */
    public function testDefaultValues(): void
    {
        $configuration = new TableConfig();
        $this->assertFalse($configuration->isTruncate());
        $this->assertSame('', $configuration->getWhere());
        $this->assertNull($configuration->getLimit());
        $this->assertSame('', $configuration->getSkipCondition());
        $this->assertSame([], $configuration->getSortOrders());
        $this->assertSame([], $configuration->getConvertersConfig());
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
