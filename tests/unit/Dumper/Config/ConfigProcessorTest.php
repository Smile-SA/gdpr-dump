<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Config;

use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Database\Metadata\MysqlMetadata;
use Smile\GdprDump\Dumper\Config\ConfigProcessor;
use Smile\GdprDump\Tests\Unit\TestCase;

class ConfigProcessorTest extends TestCase
{
    /**
     * Test the config processor.
     */
    public function testProcessor(): void
    {
        $data = [
            'tables_blacklist' => ['table1', 'not_exists'],
            'tables_whitelist' => ['table2', 'not_exists'],
            'tables' => ['table3' => ['truncate' => true], 'not_exists' => ['truncate' => true]],
        ];

        $config = new Config($data);
        $processor = $this->createConfigProcessor();
        $config = $processor->process($config);

        $this->assertSame(['table1'], $config->getExcludedTables());
        $this->assertSame(['table2'], $config->getIncludedTables());
        $this->assertSame(['table3'], array_keys($config->getTablesConfig()->all()));
    }

    /**
     * Test the config processor with wildcards.
     */
    public function testProcessorWithWildCard(): void
    {
        $data = [
            'tables_blacklist' => ['table*'],
            'tables_whitelist' => ['table*'],
            'tables' => ['table*' => ['truncate' => true]],
        ];

        $config = new Config($data);
        $processor = $this->createConfigProcessor();
        $config = $processor->process($config);

        $this->assertSame(['table1', 'table2', 'table3'], $config->getExcludedTables());
        $this->assertSame(['table1', 'table2', 'table3'], $config->getIncludedTables());
        $this->assertSame(['table1', 'table2', 'table3'], array_keys($config->getTablesConfig()->all()));
    }

    /**
     * Test the config processor with empty data.
     */
    public function testProcessorWithEmptyConfig(): void
    {
        $config = new Config();
        $processor = $this->createConfigProcessor();
        $config = $processor->process($config);

        $this->assertSame([], $config->getExcludedTables());
        $this->assertSame([], $config->getIncludedTables());
        $this->assertSame([], $config->getTablesConfig()->all());
    }

    /**
     * Create a config processor object.
     */
    private function createConfigProcessor(): ConfigProcessor
    {
        $metadataMock = $this->createMock(MysqlMetadata::class);
        $metadataMock->expects($this->atMost(1))
            ->method('getTableNames')
            ->willReturn(['table1', 'table2', 'table3']);

        return new ConfigProcessor($metadataMock);
    }
}
