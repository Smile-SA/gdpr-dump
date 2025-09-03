<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Config;

use Smile\GdprDump\Configuration\Configuration;
use Smile\GdprDump\Configuration\Definition\TableConfig;
use Smile\GdprDump\Configuration\Definition\TableConfigMap;
use Smile\GdprDump\Database\Exception\MetadataException;
use Smile\GdprDump\Database\Metadata\DatabaseMetadata;
use Smile\GdprDump\Dumper\Config\TableNameResolver;
use Smile\GdprDump\Tests\Unit\TestCase;

final class TableNameResolverTest extends TestCase
{
    /**
     * Assert that the processor resolves table names.
     */
    public function testTableNameResolution(): void
    {
        $configuration = (new Configuration())
            ->setIncludedTables(['table1'])
            ->setExcludedTables(['table2'])
            ->setTableConfigs(new TableConfigMap(['table3' => (new TableConfig())->setLimit(0)]));

        $this->createResolver()->process($configuration);

        $this->assertSame(['table1'], $configuration->getIncludedTables());
        $this->assertSame(['table2'], $configuration->getExcludedTables());

        $tableConfigs = $configuration->getTableConfigs();
        $this->assertCount(1, $tableConfigs);

        $tableConfig = $tableConfigs->get('table3');
        $this->assertNotNull($tableConfig);
        $this->assertSame(0, $tableConfig->getLimit());
    }

    /**
     * Test the config processor with a wildcard.
     */
    public function testProcessorWithWildCard(): void
    {
        $configuration = (new Configuration())
            ->setIncludedTables(['table*'])
            ->setExcludedTables(['table*'])
            ->setTableConfigs(new TableConfigMap(['table*' => (new TableConfig())->setLimit(0)]));

        $this->createResolver()->process($configuration);

        // Assert that table names were resolved
        $this->assertSame(['table1', 'table2', 'table3'], $configuration->getIncludedTables());
        $this->assertSame(['table1', 'table2', 'table3'], $configuration->getExcludedTables());

        $tableConfigs = $configuration->getTableConfigs();
        $this->assertSame(['table1', 'table2', 'table3'], $tableConfigs->getKeys());

        $tableConfig = $tableConfigs->get('table1');
        $this->assertNotNull($tableConfig);
        $this->assertSame(0, $tableConfig->getLimit());

        $tableConfig = $tableConfigs->get('table2');
        $this->assertNotNull($tableConfig);
        $this->assertSame(0, $tableConfig->getLimit());

        $tableConfig = $tableConfigs->get('table3');
        $this->assertNotNull($tableConfig);
        $this->assertSame(0, $tableConfig->getLimit());
    }

    /**
     * Test the config processor with a wildcard defined before an exact match.
     */
    public function testProcessorWithWildCardBeforeExactMatch(): void
    {
        $configuration = (new Configuration())
            ->setTableConfigs(
                new TableConfigMap([
                    'table*' => (new TableConfig())->setLimit(0),
                    'table1' => (new TableConfig())->setLimit(null),
                    'table2' => (new TableConfig())->setWhere('1=1'),
                ])
            );

        $this->createResolver()->process($configuration);

        // Assert that table names were resolved and the table data was merged
        $tableConfigs = $configuration->getTableConfigs();
        $this->assertSame(['table1', 'table2', 'table3'], $tableConfigs->getKeys());

        $tableConfig = $tableConfigs->get('table1');
        $this->assertNotNull($tableConfig);
        $this->assertNull($tableConfig->getLimit());
        $this->assertSame('', $tableConfig->getWhere());

        $tableConfig = $tableConfigs->get('table2');
        $this->assertNotNull($tableConfig);
        $this->assertSame(0, $tableConfig->getLimit());
        $this->assertSame('1=1', $tableConfig->getWhere());

        $tableConfig = $tableConfigs->get('table3');
        $this->assertNotNull($tableConfig);
        $this->assertSame(0, $tableConfig->getLimit());
        $this->assertSame('', $tableConfig->getWhere());
    }

    /**
     * Test the config processor with a wildcard defined after an exact match.
     */
    public function testProcessorWithWildCardAfterExactMatch(): void
    {
        $configuration = (new Configuration())
            ->setTableConfigs(
                new TableConfigMap([
                    'table1' => (new TableConfig())->setLimit(null),
                    'table*' => (new TableConfig())->setLimit(0),
                    'table2' => (new TableConfig())->setWhere('1=1'),
                ])
            );

        $this->createResolver()->process($configuration);

        // Assert that table names were resolved and the table data was merged
        $tableConfigs = $configuration->getTableConfigs();
        $this->assertSame(['table1', 'table2', 'table3'], $tableConfigs->getKeys());

        $tableConfig = $tableConfigs->get('table1');
        $this->assertNotNull($tableConfig);
        $this->assertSame(0, $tableConfig->getLimit());
        $this->assertSame('', $tableConfig->getWhere());

        $tableConfig = $tableConfigs->get('table2');
        $this->assertNotNull($tableConfig);
        $this->assertSame(0, $tableConfig->getLimit());
        $this->assertSame('1=1', $tableConfig->getWhere());

        $tableConfig = $tableConfigs->get('table3');
        $this->assertNotNull($tableConfig);
        $this->assertSame(0, $tableConfig->getLimit());
        $this->assertSame('', $tableConfig->getWhere());
    }

    /**
     * Test the config processor with empty data.
     */
    public function testProcessorWithEmptyConfig(): void
    {
        $configuration = new Configuration();
        $this->createResolver()->process($configuration);

        // Assert that the config was not modified
        $this->assertEmpty($configuration->getIncludedTables());
        $this->assertEmpty($configuration->getExcludedTables());
        $this->assertTrue($configuration->getTableConfigs()->isEmpty());
    }

    /**
     * Test the config processor with strict mode disabled.
     */
    public function testStrictModeDisabled(): void
    {
        $configuration = (new Configuration())
            ->setStrictSchema(false)
            ->setIncludedTables(['table1', 'not_exists'])
            ->setExcludedTables(['table2', 'not_exists'])
            ->setTableConfigs(
                new TableConfigMap([
                    'table3' => (new TableConfig())->setLimit(0),
                    'not_exists' => new TableConfig(),
                ])
            );

        $this->createResolver()->process($configuration);

        // Assert that table names were resolved
        $this->assertSame(['table1'], $configuration->getIncludedTables());
        $this->assertSame(['table2'], $configuration->getExcludedTables());

        $tableConfigs = $configuration->getTableConfigs();
        $this->assertCount(1, $tableConfigs);

        $tableConfig = $tableConfigs->get('table3');
        $this->assertNotNull($tableConfig);
        $this->assertSame(0, $tableConfig->getLimit());
    }

    /**
     * Assert that an exception is thrown when the table whitelist contains an invalid table name.
     */
    public function testStrictInvalidTableInclusion(): void
    {
        $configuration = (new Configuration())
            ->setIncludedTables(['table1', 'not_exists']);

        $this->expectException(MetadataException::class);
        $this->expectExceptionMessage('No table found with pattern "not_exists".');
        $this->createResolver()->process($configuration);
    }

    /**
     * Assert that an exception is thrown when the table blacklist contains an invalid table name.
     */
    public function testStrictModeWithInvalidTableExclusion(): void
    {
        $configuration = (new Configuration())
            ->setExcludedTables(['table2', 'not_exists']);

        $this->expectException(MetadataException::class);
        $this->expectExceptionMessage('No table found with pattern "not_exists".');
        $this->createResolver()->process($configuration);
    }

    /**
     * Assert that an exception is thrown when the tables config contains an invalid table.
     */
    public function testStrictModeWithInvalidTableConfig(): void
    {
        $configuration = (new Configuration())
            ->setTableConfigs(
                new TableConfigMap([
                    'table3' => new TableConfig(),
                    'not_exists' => new TableConfig(),
                ])
            );

        $this->expectException(MetadataException::class);
        $this->expectExceptionMessage('No table found with pattern "not_exists".');
        $this->createResolver()->process($configuration);
    }

    /**
     * Create a config processor object.
     */
    private function createResolver(): TableNameResolver
    {
        $metadataMock = $this->createMock(DatabaseMetadata::class);
        $metadataMock->expects($this->atMost(1))
            ->method('getTableNames')
            ->willReturn(['table1', 'table2', 'table3']);

        return new TableNameResolver($metadataMock);
    }
}
