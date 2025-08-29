<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Configuration;

use Smile\GdprDump\Configuration\Configuration;
use Smile\GdprDump\Configuration\Definition\DumpConfig;
use Smile\GdprDump\Configuration\Definition\FakerConfig;
use Smile\GdprDump\Configuration\Definition\FilterPropagationConfig;
use Smile\GdprDump\Configuration\Definition\TableConfig;
use Smile\GdprDump\Configuration\Definition\TableConfigMap;
use Smile\GdprDump\Configuration\Exception\InvalidQueryException;
use Smile\GdprDump\Tests\Unit\TestCase;

final class ConfigurationTest extends TestCase
{
    /**
     * Test the creation of a dumper config object.
     */
    public function testObjectCreation(): void
    {
        $dumpConfig = new DumpConfig();
        $fakerConfig = new FakerConfig();
        $filterPropagationConfig = new FilterPropagationConfig();
        $tableConfigs = new TableConfigMap([
            'table1' => new TableConfig(),
            'table2' => new TableConfig(),
        ]);
        $connectionParams = ['name' => 'tests'];
        $includedTables = ['table1'];
        $excludedTables = ['table2'];
        $sqlVariables = ['foo' => 'bar'];

        $configuration = (new Configuration())
            ->setDumpSettings($dumpConfig)
            ->setFakerConfig($fakerConfig)
            ->setFilterPropagationConfig($filterPropagationConfig)
            ->setTableConfigs($tableConfigs)
            ->setConnectionParams($connectionParams)
            ->setIncludedTables($includedTables)
            ->setExcludedTables($excludedTables)
            ->setSqlVariables($sqlVariables);

        $this->assertSame($dumpConfig, $configuration->getDumpSettings());
        $this->assertSame($fakerConfig, $configuration->getFakerConfig());
        $this->assertSame($filterPropagationConfig, $configuration->getFilterPropagationConfig());
        $this->assertSame($tableConfigs, $configuration->getTableConfigs());
        $this->assertSame($connectionParams, $configuration->getConnectionParams());
        $this->assertSame($includedTables, $configuration->getIncludedTables());
        $this->assertSame($excludedTables, $configuration->getExcludedTables());
        $this->assertSame($sqlVariables, $configuration->getSqlVariables());
    }

    /**
     * Test the default values.
     */
    public function testDefaultValues(): void
    {
        $configuration = new Configuration();
        $this->assertFalse($configuration->isStrictSchema());

        // Assert that all arrays are empty
        $this->assertTrue($configuration->getTableConfigs()->isEmpty());
        $this->assertSame([], $configuration->getConnectionParams());
        $this->assertSame([], $configuration->getIncludedTables());
        $this->assertSame([], $configuration->getExcludedTables());
        $this->assertSame([], $configuration->getSqlVariables());
    }

    /**
     * Assert that an exception is thrown when a var query contains a forbidden statement.
     */
    public function testInvalidStatementInVariableQuery(): void
    {
        $this->expectException(InvalidQueryException::class);
        (new Configuration())->setSqlVariables(['my_var' => 'select my_col from my_table; delete from my_table']);
    }
}
