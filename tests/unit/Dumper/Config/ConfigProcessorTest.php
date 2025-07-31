<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Config;

use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Database\Metadata\MetadataInterface;
use Smile\GdprDump\Dumper\Config\ConfigProcessor;
use Smile\GdprDump\Tests\Unit\TestCase;

final class ConfigProcessorTest extends TestCase
{
    /**
     * Test the config processor.
     */
    public function testProcessor(): void
    {
        $config = new Config([
            'tables_blacklist' => ['table1', 'not_exists'],
            'tables_whitelist' => ['table2', 'not_exists'],
            'tables' => ['table3' => ['truncate' => true], 'not_exists' => ['truncate' => true]],
        ]);

        $processor = $this->createConfigProcessor();
        $processor->process($config);

        // Assert that table names were resolved
        $this->assertSame(['table1'], $config->get('tables_blacklist'));
        $this->assertSame(['table2'], $config->get('tables_whitelist'));
        $this->assertIsArray($config->get('tables'));
        $this->assertSame(['table3'], array_keys($config->get('tables')));
    }

    /**
     * Test the config processor with wildcards.
     */
    public function testProcessorWithWildCard(): void
    {
        $config = new Config([
            'tables_blacklist' => ['table*'],
            'tables_whitelist' => ['table*'],
            'tables' => ['table*' => ['truncate' => true]],
        ]);

        $processor = $this->createConfigProcessor();
        $processor->process($config);

        // Assert that table names were resolved
        $this->assertSame(['table1', 'table2', 'table3'], $config->get('tables_blacklist'));
        $this->assertSame(['table1', 'table2', 'table3'], $config->get('tables_whitelist'));
        $this->assertIsArray($config->get('tables'));
        $this->assertSame(['table1', 'table2', 'table3'], array_keys($config->get('tables')));
    }

    /**
     * Test the config processor with empty data.
     */
    public function testProcessorWithEmptyConfig(): void
    {
        $config = new Config();
        $processor = $this->createConfigProcessor();
        $processor->process($config);

        // Assert that the config was not modified
        $this->assertSame([], $config->toArray());
    }

    /**
     * Create a config processor object.
     */
    private function createConfigProcessor(): ConfigProcessor
    {
        $metadataMock = $this->createMock(MetadataInterface::class);
        $metadataMock->expects($this->atMost(1))
            ->method('getTableNames')
            ->willReturn(['table1', 'table2', 'table3']);

        return new ConfigProcessor($metadataMock);
    }
}
