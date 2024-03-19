<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Functional\Dumper\Config;

use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Dumper\Config\ConfigProcessor;
use Smile\GdprDump\Tests\Functional\TestCase;

class ConfigProcessorTest extends TestCase
{
    /**
     * Test the table name resolution.
     */
    public function testTableNameResolution(): void
    {
        $data = [
            'tables_whitelist' => ['cust*', 'notExist*'],
            'tables_blacklist' => ['stor*', 'notExist*'],
            'tables' => [
                'cust*' => [],
                'notExist*' => [],
            ],
        ];

        // Process the configuration
        $processor = $this->createConfigProcessor();
        $config = $processor->process(new Config($data));

        // Check if the table names were resolved
        $this->assertSame(['customers'], $config->getTablesWhitelist());
        $this->assertSame(['stores'], $config->getTablesBlacklist());

        $tablesConfig = $config->getTablesConfig()->all();
        $this->assertArrayHasKey('customers', $tablesConfig);
        $this->assertArrayNotHasKey('cust*', $tablesConfig);
        $this->assertArrayNotHasKey('notExist*', $tablesConfig);
    }

    /**
     * Test the config processor behavior with an empty configuration.
     */
    public function testWithEmptyConfig(): void
    {
        // Create the config processor
        $processor = $this->createConfigProcessor();

        // Process the configuration
        $config = $processor->process(new Config([]));
        $this->assertEmpty($config->getTablesBlacklist());
        $this->assertEmpty($config->getTablesWhitelist());
        $this->assertEmpty($config->getTablesConfig()->all());
    }

    /**
     * Create a config processor object.
     */
    private function createConfigProcessor(): ConfigProcessor
    {
        $metadata = $this->getDatabase()->getMetadata();

        return new ConfigProcessor($metadata);
    }
}
