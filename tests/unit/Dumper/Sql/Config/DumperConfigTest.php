<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Sql\Config;

use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Dumper\Sql\Config\DumperConfig;
use Smile\GdprDump\Dumper\Sql\Config\Table\TableConfig;
use Smile\GdprDump\Tests\Unit\TestCase;
use Symfony\Component\Yaml\Yaml;

class DumperConfigTest extends TestCase
{
    /**
     * Test the "tables_whitelist" parameter.
     */
    public function testTablesWhitelist()
    {
        $whitelist = ['table1', 'table2'];
        $config = $this->createConfig(['tables_whitelist' => $whitelist]);
        $this->assertSame($whitelist, $config->getTablesWhitelist());
    }

    /**
     * Test the "tables_blacklist" parameter.
     */
    public function testTablesBlacklist()
    {
        $blacklist = ['table1', 'table2'];
        $config = $this->createConfig(['tables_blacklist' => $blacklist]);
        $this->assertSame($blacklist, $config->getTablesBlacklist());
    }

    /**
     * Test if a dump file is created.
     */
    public function testTablesData()
    {
        $tablesData = [
            'tables' => [
                'table1' => ['truncate' => true],
                'table2' => ['limit' => 1],
                'table3' => ['orderBy' => 'field1'],
                'table4' => ['converters' => ['field1' => 'randomizeEmail']],
            ],
        ];

        $config = $this->createConfig($tablesData);

        $this->assertSame(['table1'], $config->getTablesToTruncate());
        $this->assertSame(['table1', 'table2'], $config->getTablesToFilter());
        $this->assertSame(['table3'], $config->getTablesToSort());
        $this->assertCount(4, $config->getTablesConfig());
        $this->assertInstanceOf(TableConfig::class, $config->getTableConfig('table1'));
    }

    /**
     * Test the dump settings.
     */
    public function testDumpSettings()
    {
        $config = $this->createConfig(['dump' => ['output' => 'dump.sql', 'hex_blob' => true]]);

        // Dump output
        $this->assertSame('dump.sql', $config->getDumpOutput());

        // Dump settings
        $settings = $config->getDumpSettings();
        $this->assertArrayHasKey('hex_blob', $settings);

        if (array_key_exists('hex_blob', $settings)) {
            $this->assertTrue($settings['hex_blob']);
        }
    }

    /**
     * Test the default config values.
     */
    public function testDefaultValues()
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
     * Test if an exception is thrown when an invalid parameter is used.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testInvalidDumpParameter()
    {
        $this->createConfig(['dump' => ['notExists' => true]]);
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
