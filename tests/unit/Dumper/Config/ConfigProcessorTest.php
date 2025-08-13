<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Config;

use RuntimeException;
use Smile\GdprDump\Configuration\Configuration;
use Smile\GdprDump\Configuration\Definition\TableConfig;
use Smile\GdprDump\Database\Metadata\DatabaseMetadata;
use Smile\GdprDump\Dumper\Config\ConfigProcessor;
use Smile\GdprDump\Tests\Unit\TestCase;

final class ConfigProcessorTest extends TestCase
{
    /**
     * Assert that the processor resolves table names.
     */
    public function testTableNameResolution(): void
    {
        $configuration = (new Configuration())
            ->setIncludedTables(['table1', 'not_exists'])
            ->setExcludedTables(['table2', 'not_exists'])
            ->setTablesConfig([
                'table3' => (new TableConfig())->setTruncate(true),
                'not_exists' => (new TableConfig()),
            ]);

        $this->createConfigProcessor()->process($configuration);

        // Assert that table names were resolved
        $this->assertSame(['table1'], $configuration->getIncludedTables());
        $this->assertSame(['table2'], $configuration->getExcludedTables());

        $tablesConfig = $configuration->getTablesConfig();
        $this->assertCount(1, $tablesConfig);
        $this->assertArrayHasKey('table3', $tablesConfig);
        $this->assertTrue($tablesConfig['table3']->isTruncate());
    }

    /**
     * Test the config processor with a wildcard.
     */
    public function testProcessorWithWildCard(): void
    {
        $configuration = (new Configuration())
            ->setIncludedTables(['table*'])
            ->setExcludedTables(['table*'])
            ->setTablesConfig([
                'table*' => (new TableConfig())->setTruncate(true),
            ]);

        $this->createConfigProcessor()->process($configuration);

        // Assert that table names were resolved
        $this->assertSame(['table1', 'table2', 'table3'], $configuration->getIncludedTables());
        $this->assertSame(['table1', 'table2', 'table3'], $configuration->getExcludedTables());

        $tablesConfig = $configuration->getTablesConfig();
        $this->assertSame(['table1', 'table2', 'table3'], array_keys($tablesConfig));

        $expectedTableConfig = (new TableConfig())->setTruncate(true);
        $this->assertEquals($expectedTableConfig, $tablesConfig['table1']);
        $this->assertEquals($expectedTableConfig, $tablesConfig['table2']);
        $this->assertEquals($expectedTableConfig, $tablesConfig['table3']);
    }

    /**
     * Test the config processor with a wildcard defined before an exact match.
     */
    public function testProcessorWithWildCardBeforeExactMatch(): void
    {
        $configuration = (new Configuration())
            ->setTablesConfig([
                'table*' => (new TableConfig())->setTruncate(true),
                'table1' => (new TableConfig())->setTruncate(false),
                'table2' => (new TableConfig())->setWhere('1=1'),
            ]);

        $this->createConfigProcessor()->process($configuration);

        // Assert that table names were resolved and the table data was merged
        $tablesConfig = $configuration->getTablesConfig();
        $this->assertSame(['table1', 'table2', 'table3'], array_keys($tablesConfig));
        $this->assertEquals((new TableConfig())->setTruncate(false), $tablesConfig['table1']);
        $this->assertEquals((new TableConfig())->setTruncate(true)->setWhere('1=1'), $tablesConfig['table2']);
        $this->assertEquals((new TableConfig())->setTruncate(true), $tablesConfig['table3']);
    }

    /**
     * Test the config processor with a wildcard defined after an exact match.
     */
    public function testProcessorWithWildCardAfterExactMatch(): void
    {
        $configuration = (new Configuration())
            ->setTablesConfig([
                'table1' => (new TableConfig())->setTruncate(false),
                'table*' => (new TableConfig())->setTruncate(true),
                'table2' => (new TableConfig())->setWhere('1=1'),
            ]);

        $this->createConfigProcessor()->process($configuration);

        // Assert that table names were resolved and the table data was merged
        $tablesConfig = $configuration->getTablesConfig();
        $this->assertSame(['table1', 'table2', 'table3'], array_keys($tablesConfig));
        $this->assertEquals((new TableConfig())->setTruncate(true), $tablesConfig['table1']);
        $this->assertEquals((new TableConfig())->setTruncate(true)->setWhere('1=1'), $tablesConfig['table2']);
        $this->assertEquals((new TableConfig())->setTruncate(true), $tablesConfig['table3']);
    }

    /**
     * Test the config processor with empty data.
     */
    public function testProcessorWithEmptyConfig(): void
    {
        $configuration = new Configuration();
        $this->createConfigProcessor()->process($configuration);

        // Assert that the config was not modified
        $this->assertEmpty($configuration->getIncludedTables());
        $this->assertEmpty($configuration->getExcludedTables());
        $this->assertEmpty($configuration->getTablesConfig());
    }

    /**
     * Test the config processor with strict mode enabled.
     */
    public function testStrictMode(): void
    {
        $configuration = (new Configuration())
            ->setStrictSchema(true)
            ->setIncludedTables(['table1'])
            ->setExcludedTables(['table2'])
            ->setTablesConfig(['table3' => (new TableConfig())->setTruncate(true)]);

        $this->createConfigProcessor()->process($configuration);

        $this->assertSame(['table1'], $configuration->getIncludedTables());
        $this->assertSame(['table2'], $configuration->getExcludedTables());

        $tablesConfig = $configuration->getTablesConfig();
        $this->assertCount(1, $tablesConfig);
        $this->assertArrayHasKey('table3', $tablesConfig);
        $this->assertTrue($tablesConfig['table3']->isTruncate());
    }

    /**
     * Assert that an exception is thrown in strict mode when the table whitelist contains an invalid table name.
     */
    public function testStrictModeWithInvalidTableInclusion(): void
    {
        $configuration = (new Configuration())
            ->setStrictSchema(true)
            ->setIncludedTables(['table1', 'not_exists']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No table found with pattern "not_exists".');
        $this->createConfigProcessor()->process($configuration);
    }

    /**
     * Assert that an exception is thrown in strict mode when the table blacklist contains an invalid table name.
     */
    public function testStrictModeWithInvalidTableExclusion(): void
    {
        $configuration = (new Configuration())
            ->setStrictSchema(true)
            ->setExcludedTables(['table2', 'not_exists']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No table found with pattern "not_exists".');
        $this->createConfigProcessor()->process($configuration);
    }

    /**
     * Assert that an exception is thrown in strict mode when the tables config contains an invalid table.
     */
    public function testStrictModeWithInvalidTableConfig(): void
    {
        $configuration = (new Configuration())
            ->setStrictSchema(true)
            ->setTablesConfig([
                'table3' => (new TableConfig()),
                'not_exists' => (new TableConfig()),
            ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No table found with pattern "not_exists".');
        $this->createConfigProcessor()->process($configuration);
    }

    /**
     * Create a config processor object.
     */
    private function createConfigProcessor(): ConfigProcessor
    {
        $metadataMock = $this->createMock(DatabaseMetadata::class);
        $metadataMock->expects($this->atMost(1))
            ->method('getTableNames')
            ->willReturn(['table1', 'table2', 'table3']);

        return new ConfigProcessor($metadataMock);
    }
}
