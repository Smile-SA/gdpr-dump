<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Functional\Dumper\Sql\Config;

use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Dumper\Sql\Config\ConfigProcessor;
use Smile\GdprDump\Dumper\Sql\Config\DumperConfig;
use Smile\GdprDump\Tests\Functional\DatabaseTestCase;

class ConfigProcessorTest extends DatabaseTestCase
{
    /**
     * Test the table name resolution.
     */
    public function testTableNameResolution()
    {
        $data = [
            'tables_whitelist' => ['cust*', 'notExist*'],
            'tables_blacklist' => ['stor*', 'notExist*'],
            'tables' => [
                'cust*' => [],
                'notExist*' => [],
            ],
        ];

        // Create the config processor
        $processor = $this->createConfigProcessor();

        // Process the configuration
        $config = $processor->process(new Config($data));
        $this->assertInstanceOf(DumperConfig::class, $config);

        // Check if the table names were resolved
        $this->assertSame(['customers'], $config->getTablesWhitelist());
        $this->assertSame(['stores'], $config->getTablesBlacklist());
        $this->assertArrayHasKey('customers', $config->getTablesConfig());
        $this->assertArrayNotHasKey('cust*', $config->getTablesConfig());
        $this->assertArrayNotHasKey('notExist*', $config->getTablesConfig());
    }

    /**
     * Test the config processor behavior with an empty configuration.
     */
    public function testWithEmptyConfig()
    {
        // Create the config processor
        $processor = $this->createConfigProcessor();

        // Process the configuration
        $config = $processor->process(new Config([]));
        $this->assertEmpty($config->getTablesBlacklist());
        $this->assertEmpty($config->getTablesWhitelist());
        $this->assertEmpty($config->getTablesConfig());
    }

    /**
     * Create a config processor object.
     *
     * @return ConfigProcessor
     */
    private function createConfigProcessor(): ConfigProcessor
    {
        $connection = $this->getConnection();

        return new ConfigProcessor($connection);
    }
}
