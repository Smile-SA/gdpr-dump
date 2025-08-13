<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Config;

use Smile\GdprDump\Config\Definition\DumpConfig;
use Smile\GdprDump\Config\Definition\FakerConfig;
use Smile\GdprDump\Config\Definition\FilterPropagationConfig;
use Smile\GdprDump\Config\Definition\Table\SortOrder;
use Smile\GdprDump\Config\Definition\TableConfig;
use Smile\GdprDump\Config\DumperConfig;
use Smile\GdprDump\Tests\Unit\TestCase;
use UnexpectedValueException;

final class DumperConfigTest extends TestCase
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

        $config = (new DumperConfig())
            ->setDumpSettings($dumpConfig)
            ->setFakerConfig($fakerConfig)
            ->setFilterPropagationConfig($filterPropagationConfig)
            ->setTablesConfig($tablesConfig)
            ->setConnectionParams($connectionParams)
            ->setIncludedTables($includedTables)
            ->setExcludedTables($excludedTables)
            ->setVarQueries($varQueries);

        $this->assertSame($dumpConfig, $config->getDumpSettings());
        $this->assertSame($fakerConfig, $config->getFakerConfig());
        $this->assertSame($filterPropagationConfig, $config->getFilterPropagationConfig());
        $this->assertSame($tablesConfig, $config->getTablesConfig());
        $this->assertSame($connectionParams, $config->getConnectionParams());
        $this->assertSame($includedTables, $config->getIncludedTables());
        $this->assertSame($excludedTables, $config->getExcludedTables());
        $this->assertSame($varQueries, $config->getVarQueries());
        $this->assertSame(['table2', 'table3'], $config->getTablesToFilter());
        $this->assertSame(['table4'], $config->getTablesToSort());
        $this->assertSame(['table1'], $config->getTablesToTruncate());
    }

    /**
     * Test the default values.
     */
    public function testDefaultValues(): void
    {
        $config = new DumperConfig();

        // Assert that the objects are properly loaded
        $this->assertInstanceOf(DumpConfig::class, $config->getDumpSettings());
        $this->assertInstanceOf(FakerConfig::class, $config->getFakerConfig());
        $this->assertInstanceOf(FilterPropagationConfig::class, $config->getFilterPropagationConfig());

        // Assert that other values are properly set
        $this->assertSame([], $config->getTablesConfig());
        $this->assertSame([], $config->getConnectionParams());
        $this->assertSame([], $config->getIncludedTables());
        $this->assertSame([], $config->getExcludedTables());
        $this->assertSame([], $config->getVarQueries());
        $this->assertSame([], $config->getTablesToFilter());
        $this->assertSame([], $config->getTablesToSort());
        $this->assertSame([], $config->getTablesToTruncate());
    }

    /**
     * Assert that an exception is thrown when a var query contains a forbidden statement.
     */
    public function testInvalidStatementInVariableQuery(): void
    {
        $this->expectException(UnexpectedValueException::class);
        (new DumperConfig())->setVarQueries(['my_var' => 'select my_col from my_table; delete from my_table']);
    }
}
