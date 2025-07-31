<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Config;

use RuntimeException;
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

        $this->createConfigProcessor()->process($config);

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

        $this->createConfigProcessor()->process($config);

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
        $this->createConfigProcessor()->process($config);

        // Assert that the config was not modified
        $this->assertSame([], $config->toArray());
    }

    /**
     * Test the config processor with strict mode enabled.
     */
    public function testStrictMode(): void
    {
        $config = new Config([
            'strict' => true,
            'tables_blacklist' => ['table1'],
            'tables_whitelist' => ['table2'],
            'tables' => ['table*' => ['truncate' => true]],
        ]);

        $this->createConfigProcessor()->process($config);

        $this->assertSame(['table1'], $config->get('tables_blacklist'));
        $this->assertSame(['table2'], $config->get('tables_whitelist'));
        $this->assertIsArray($config->get('tables'));
        $this->assertSame(['table1', 'table2', 'table3'], array_keys($config->get('tables')));
    }

    /**
     * Assert that an exception is thrown in strict mode when the table blacklist contains an invalid table name.
     */
    public function testStrictModeWithInvalidTableExclusion(): void
    {
        $config = new Config([
            'strict_schema' => true,
            'tables_blacklist' => ['table1', 'not_exists'],
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No table found with pattern "not_exists".');
        $this->createConfigProcessor()->process($config);
    }

    /**
     * Assert that an exception is thrown in strict mode when the table whitelist contains an invalid table name.
     */
    public function testStrictModeWithInvalidTableInclusion(): void
    {
        $config = new Config([
            'strict_schema' => true,
            'tables_whitelist' => ['table2', 'not_exists'],
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No table found with pattern "not_exists".');
        $this->createConfigProcessor()->process($config);
    }

    /**
     * Assert that an exception is thrown in strict mode when the tables config contains an invalid table.
     */
    public function testStrictModeWithInvalidTableConfig(): void
    {
        $config = new Config([
            'strict_schema' => true,
            'tables' => ['table3' => ['truncate' => true], 'not_exists' => ['truncate' => true]],
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No table found with pattern "not_exists".');
        $this->createConfigProcessor()->process($config);
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
