<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Sql\Config;

use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Dumper\Sql\Config\ConfigProcessor;
use Smile\GdprDump\Dumper\Sql\Metadata\MysqlMetadata;
use Smile\GdprDump\Tests\Unit\TestCase;

class ConfigProcessorTest extends TestCase
{
    /**
     * Test the config processor.
     */
    public function testProcessor()
    {
        $data = [
            'tables_blacklist' => ['table1', 'not_exists'],
            'tables_whitelist' => ['table2', 'not_exists'],
            'tables' => ['table3' => ['truncate' => true], 'not_exists' => ['truncate' => true]],
        ];

        $config = new Config($data);
        $processor = $this->createConfigProcessor();
        $config = $processor->process($config);

        $this->assertSame(['table1'], $config->getTablesBlacklist());
        $this->assertSame(['table2'], $config->getTablesWhitelist());
        $this->assertSame(['table3'], array_keys($config->getTablesConfig()));
    }

    /**
     * Test the config processor with wildcards.
     */
    public function testProcessorWithWildCard()
    {
        $data = [
            'tables_blacklist' => ['table*'],
            'tables_whitelist' => ['table*'],
            'tables' => ['table*' => ['truncate' => true]],
        ];

        $config = new Config($data);
        $processor = $this->createConfigProcessor();
        $config = $processor->process($config);

        $this->assertSame(['table1', 'table2', 'table3'], $config->getTablesBlacklist());
        $this->assertSame(['table1', 'table2', 'table3'], $config->getTablesWhitelist());
        $this->assertSame(['table1', 'table2', 'table3'], array_keys($config->getTablesConfig()));
    }

    /**
     * Test the config processor with empty data.
     */
    public function testProcessorWithEmptyConfig()
    {
        $config = new Config();
        $processor = $this->createConfigProcessor();
        $config = $processor->process($config);

        $this->assertSame([], $config->getTablesBlacklist());
        $this->assertSame([], $config->getTablesWhitelist());
        $this->assertSame([], $config->getTablesConfig());
    }

    /**
     * Create a config processor object.
     *
     * @return ConfigProcessor
     */
    private function createConfigProcessor(): ConfigProcessor
    {
        $metadataMock = $this->createMock(MysqlMetadata::class);
        $metadataMock->method('getTableNames')
            ->willReturn(['table1', 'table2', 'table3']);

        return new ConfigProcessor($metadataMock);
    }
}
