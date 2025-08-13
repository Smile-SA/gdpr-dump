<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Config;

use Smile\GdprDump\Configuration\Configuration;
use Smile\GdprDump\Configuration\Definition\DumpConfig;
use Smile\GdprDump\Configuration\Definition\FakerConfig;
use Smile\GdprDump\Configuration\Definition\FilterPropagationConfig;
use Smile\GdprDump\Configuration\Definition\Table\SortOrder;
use Smile\GdprDump\Configuration\Definition\TableConfig;
use Smile\GdprDump\Tests\Unit\TestCase;
use UnexpectedValueException;

final class ConfigTest extends TestCase
{
    /**
     * Test the creation of a dumper config object.
     */
    public function testObjectCreation(): void
    {
        $dumpConfig = new DumpConfig();
        $fakerConfig = new FakerConfig();
        $filterPropagationConfig = new FilterPropagationConfig();
        $tablesConfig = [
            'table1' => (new TableConfig())->setTruncate(true), // to truncate
            'table2' => (new TableConfig())->setLimit(10), // to filter
            'table3' => (new TableConfig())->setWhere('1=1'), // to filter
            'table4' => (new TableConfig())->setSortOrders([new SortOrder('id')]), // to sort
            'table5' => (new TableConfig())->setLimit(0), // value 0 must be ignored
        ];
        $connectionParams = ['name' => 'tests'];
        $includedTables = ['table1'];
        $excludedTables = ['table2'];
        $varQueries = ['foo' => 'bar'];

        $configuration = (new Configuration())
            ->setDumpSettings($dumpConfig)
            ->setFakerConfig($fakerConfig)
            ->setFilterPropagationConfig($filterPropagationConfig)
            ->setTablesConfig($tablesConfig)
            ->setConnectionParams($connectionParams)
            ->setIncludedTables($includedTables)
            ->setExcludedTables($excludedTables)
            ->setVarQueries($varQueries);

        $this->assertSame($dumpConfig, $configuration->getDumpSettings());
        $this->assertSame($fakerConfig, $configuration->getFakerConfig());
        $this->assertSame($filterPropagationConfig, $configuration->getFilterPropagationConfig());
        $this->assertSame($tablesConfig, $configuration->getTablesConfig());
        $this->assertSame($connectionParams, $configuration->getConnectionParams());
        $this->assertSame($includedTables, $configuration->getIncludedTables());
        $this->assertSame($excludedTables, $configuration->getExcludedTables());
        $this->assertSame($varQueries, $configuration->getVarQueries());
        $this->assertSame(['table2', 'table3'], $configuration->getTablesToFilter());
        $this->assertSame(['table4'], $configuration->getTablesToSort());
        $this->assertSame(['table1'], $configuration->getTablesToTruncate());
    }

    /**
     * Test the default values.
     */
    public function testDefaultValues(): void
    {
        $configuration = new Configuration();

        // Assert that the objects are properly loaded
        $this->assertInstanceOf(DumpConfig::class, $configuration->getDumpSettings());
        $this->assertInstanceOf(FakerConfig::class, $configuration->getFakerConfig());
        $this->assertInstanceOf(FilterPropagationConfig::class, $configuration->getFilterPropagationConfig());

        // Assert that other values are properly set
        $this->assertSame([], $configuration->getTablesConfig());
        $this->assertSame([], $configuration->getConnectionParams());
        $this->assertSame([], $configuration->getIncludedTables());
        $this->assertSame([], $configuration->getExcludedTables());
        $this->assertSame([], $configuration->getVarQueries());
        $this->assertSame([], $configuration->getTablesToFilter());
        $this->assertSame([], $configuration->getTablesToSort());
        $this->assertSame([], $configuration->getTablesToTruncate());
    }

    /**
     * Assert that an exception is thrown when a var query contains a forbidden statement.
     */
    public function testInvalidStatementInVariableQuery(): void
    {
        $this->expectException(UnexpectedValueException::class);
        (new Configuration())->setVarQueries(['my_var' => 'select my_col from my_table; delete from my_table']);
    }
}
