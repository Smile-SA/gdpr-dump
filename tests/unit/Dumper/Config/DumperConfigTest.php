<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Config;

use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Dumper\Config\DumperConfig;
use Smile\GdprDump\Tests\Unit\TestCase;
use UnexpectedValueException;

final class DumperConfigTest extends TestCase
{
    /**
     * Test the "tables_whitelist" parameter.
     */
    public function testIncludedTables(): void
    {
        $includedTables = ['table1', 'table2'];
        $config = $this->createConfig(['tables_whitelist' => $includedTables]);
        $this->assertSame($includedTables, $config->getIncludedTables());
    }

    /**
     * Test the "tables_blacklist" parameter.
     */
    public function testExcludedTables(): void
    {
        $excludedTables = ['table1', 'table2'];
        $config = $this->createConfig(['tables_blacklist' => $excludedTables]);
        $this->assertSame($excludedTables, $config->getExcludedTables());
    }

    /**
     * Test if a dump file is created.
     */
    public function testTablesData(): void
    {
        $data = [
            'tables' => [
                'table1' => ['truncate' => true],
                'table2' => ['limit' => 1],
                'table3' => ['order_by' => 'field1'],
                'table4' => ['converters' => ['field1' => ['converter' => 'randomizeEmail']]],
            ],
        ];

        $config = $this->createConfig($data);

        $this->assertSame(['table1'], $config->getTablesToTruncate());
        $this->assertSame(['table1', 'table2'], $config->getTablesToFilter());
        $this->assertSame(['table3'], $config->getTablesToSort());
        $this->assertCount(4, $config->getTablesConfig()->all());
        $this->assertSame(['table1', 'table2', 'table3', 'table4'], array_keys($config->getTablesConfig()->all()));
    }

    /**
     * Test dump settings.
     */
    public function testDumpSettings(): void
    {
        $config = $this->createConfig(['dump' => ['output' => 'dump.sql', 'hex_blob' => true]]);

        // Dump output
        $this->assertSame('dump.sql', $config->getDumpOutput());

        // Dump settings
        $settings = $config->getDumpSettings();
        $this->assertArrayHasKey('hex_blob', $settings);
        $this->assertTrue($settings['hex_blob']);
    }

    /**
     * Test filter propagation settings.
     */
    public function testFilterPropagationSettings(): void
    {
        $data = [
            'filter_propagation' => [
                'enabled' => true,
                'ignored_foreign_keys' => ['fk1', 'fk2'],
            ],
        ];

        $config = $this->createConfig($data);

        $this->assertSame($data['filter_propagation']['enabled'], $config->getFilterPropagationSettings()->isEnabled());
        $this->assertSame(
            $data['filter_propagation']['ignored_foreign_keys'],
            $config->getFilterPropagationSettings()->getIgnoredForeignKeys()
        );
    }

    /**
     * Test faker settings.
     */
    public function testFakerSettings(): void
    {
        $config = $this->createConfig(['faker' => ['locale' => 'en_US']]);
        $this->assertSame('en_US', $config->getFakerSettings()->getLocale());
    }

    /**
     * Test the variables to initialize with SQL queries.
     */
    public function testVarQueries(): void
    {
        $queries = [
            'my_var' => 'select my_col from my_table where other_col = "something"',
        ];

        $config = $this->createConfig(['variables' => $queries]);
        $this->assertSame($queries, $config->getVarQueries());
    }

    /**
     * Test the default config values.
     */
    public function testDefaultValues(): void
    {
        $config = $this->createConfig([]);

        $this->assertSame([], $config->getDumpSettings());
        $this->assertSame([], $config->getIncludedTables());
        $this->assertSame([], $config->getExcludedTables());
        $this->assertSame([], $config->getTablesToSort());
        $this->assertSame([], $config->getTablesToFilter());
        $this->assertSame([], $config->getTablesToTruncate());
        $this->assertSame([], $config->getTablesConfig()->all());
        $this->assertTrue($config->getFilterPropagationSettings()->isEnabled());
        $this->assertSame([], $config->getFilterPropagationSettings()->getIgnoredForeignKeys());
        $this->assertSame('', $config->getFakerSettings()->getLocale());
    }

    /**
     * Assert that an exception is thrown when a var query contains a forbidden statement.
     */
    public function testInvalidStatementInVariableQuery(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->createConfig([
            'variables' => ['my_var' => 'select my_col from my_table; delete from my_table'],
        ]);
    }

    /**
     * Assert that an exception is thrown when an init command contains a forbidden statement.
     */
    public function testInvalidStatementInInitCommand(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->createConfig([
            'dump' => [
                'init_commands' => [
                    'my_var' => 'select my_col from my_table; delete from my_table',
                ],
            ],
        ]);
    }

    /**
     * Create a dumper config object that stores the specified data.
     */
    private function createConfig(array $data): DumperConfig
    {
        $config = new Config($data);

        return new DumperConfig($config);
    }
}
