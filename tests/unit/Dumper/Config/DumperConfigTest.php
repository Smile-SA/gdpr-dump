<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Config;

use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Dumper\Config\DumperConfig;
use Smile\GdprDump\Dumper\Config\Validation\ValidationException;
use Smile\GdprDump\Tests\Unit\TestCase;

class DumperConfigTest extends TestCase
{
    /**
     * Test the "tables_whitelist" parameter.
     */
    public function testTablesWhitelist(): void
    {
        $whitelist = ['table1', 'table2'];
        $config = $this->createConfig(['tables_whitelist' => $whitelist]);
        $this->assertSame($whitelist, $config->getTablesWhitelist());
    }

    /**
     * Test the "tables_blacklist" parameter.
     */
    public function testTablesBlacklist(): void
    {
        $blacklist = ['table1', 'table2'];
        $config = $this->createConfig(['tables_blacklist' => $blacklist]);
        $this->assertSame($blacklist, $config->getTablesBlacklist());
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

        $this->assertSame([], $config->getTablesWhitelist());
        $this->assertSame([], $config->getTablesBlacklist());
        $this->assertSame([], $config->getTablesToSort());
        $this->assertSame([], $config->getTablesToFilter());
        $this->assertSame([], $config->getTablesToTruncate());
        $this->assertSame([], $config->getTablesConfig()->all());
        $this->assertSame('php://stdout', $config->getDumpOutput());
        $this->assertTrue($config->getFilterPropagationSettings()->isEnabled());
        $this->assertSame([], $config->getFilterPropagationSettings()->getIgnoredForeignKeys());

        // Test these values because they differ from MySQLDump-PHP
        $settings = $config->getDumpSettings();
        $this->assertArrayHasKey('add_drop_table', $settings);
        $this->assertTrue($settings['add_drop_table']);
        $this->assertArrayHasKey('hex_blob', $settings);
        $this->assertFalse($settings['hex_blob']);
        $this->assertArrayHasKey('lock_tables', $settings);
        $this->assertFalse($settings['lock_tables']);

        $this->assertSame('', $config->getFakerSettings()->getLocale());
    }

    /**
     * Assert that an exception is thrown when an invalid parameter is used.
     */
    public function testInvalidDumpParameter(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConfig(['dump' => ['not_exists' => true]]);
    }

    /**
     * Assert that an exception is thrown when a var query contains a forbidden statement.
     */
    public function testInvalidStatementInQuery(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConfig(['variables' => ['my_var' => 'select my_col from my_table; delete from my_table']]);
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
