<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Config;

use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Dumper\Config\DumperConfig;
use Smile\GdprDump\Dumper\Config\Table\TableConfig;
use Smile\GdprDump\Dumper\Config\Validation\ValidationException;
use Smile\GdprDump\Tests\Unit\TestCase;
use UnexpectedValueException;

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
        $configData = [
            'tables' => [
                'table1' => ['truncate' => true],
                'table2' => ['limit' => 1],
                'table3' => ['order_by' => 'field1'],
                'table4' => ['converters' => ['field1' => ['converter' => 'randomizeEmail']]],
            ],
        ];

        $config = $this->createConfig($configData);

        $this->assertSame(['table1'], $config->getTablesToTruncate());
        $this->assertSame(['table1', 'table2'], $config->getTablesToFilter());
        $this->assertSame(['table3'], $config->getTablesToSort());
        $this->assertCount(4, $config->getTablesConfig());
        $this->assertInstanceOf(TableConfig::class, $config->getTableConfig('table1'));
    }

    /**
     * Test the dump settings.
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

        $this->assertEmpty($config->getTablesWhitelist());
        $this->assertEmpty($config->getTablesBlacklist());
        $this->assertEmpty($config->getTablesToSort());
        $this->assertEmpty($config->getTablesToFilter());
        $this->assertEmpty($config->getTablesToTruncate());
        $this->assertEmpty($config->getTablesConfig());
        $this->assertSame('php://stdout', $config->getDumpOutput());

        // Test these values because they differ from MySQLDump-PHP
        $settings = $config->getDumpSettings();
        $this->assertTrue($settings['add_drop_table']);
        $this->assertFalse($settings['hex_blob']);
        $this->assertFalse($settings['lock_tables']);
    }

    /**
     * Assert that an exception is thrown when an invalid parameter is used.
     */
    public function testInvalidDumpParameter(): void
    {
        $this->expectException(UnexpectedValueException::class);
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
     *
     * @param array $data
     * @return DumperConfig
     */
    private function createConfig(array $data): DumperConfig
    {
        $config = new Config($data);

        return new DumperConfig($config);
    }
}
