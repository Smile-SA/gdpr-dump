<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Tests\Dumper\Sql\Config;

use Smile\Anonymizer\Config\Config;
use Smile\Anonymizer\Dumper\Sql\Config\DumperConfig;
use Smile\Anonymizer\Dumper\Sql\Config\Table\TableConfig;
use Smile\Anonymizer\Tests\TestCase;
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
        $config = $this->createConfigFromFile($this->getTestConfigFile());

        $this->assertSame(['stores'], $config->getTablesToFilter());
        $this->assertSame(['customers'], $config->getTablesToSort());
        $this->assertCount(3, $config->getTablesConfig());
        $this->assertInstanceOf(TableConfig::class, $config->getTableConfig('customers'));
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

    /**
     * Create a dumper config object that stores the specified config file data.
     *
     * @param string $fileName
     * @return DumperConfig
     */
    private function createConfigFromFile(string $fileName): DumperConfig
    {
        $data = Yaml::parseFile($fileName);
        $config = new Config($data);

        return new DumperConfig($config);
    }
}
