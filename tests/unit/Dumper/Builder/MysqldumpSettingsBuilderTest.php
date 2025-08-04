<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Builder;

use RuntimeException;
use Smile\GdprDump\Dumper\Builder\MysqldumpSettingsBuilder;
use Smile\GdprDump\Dumper\Config\DumperConfigInterface;
use Smile\GdprDump\Tests\Unit\TestCase;

final class MysqldumpSettingsBuilderTest extends TestCase
{
    /**
     * Test the builder when all settings are defined.
     */
    public function testAllSettings(): void
    {
        $dumpSettings = [
            'add_drop_database' => false,
            'add_drop_table' => true,
            'add_drop_trigger' => true,
            'add_locks' => true,
            'complete_insert' => false,
            'compress' => 'gzip',
            'default_character_set' => 'utf8',
            'disable_keys' => true,
            'events' => false,
            'extended_insert' => true,
            'hex_blob' => true,
            'init_commands' => [],
            'insert_ignore' => false,
            'lock_tables' => false,
            'net_buffer_length' => 1000000,
            'no_autocommit' => true,
            'no_create_info' => false,
            'routines' => false,
            'single_transaction' => true,
            'skip_comments' => false,
            'skip_definer' => false,
            'skip_dump_date' => false,
            'skip_triggers' => false,
            'skip_tz_utc' => false,
        ];

        $includedTables = ['tbl1', 'tbl2'];
        $excludedTables = ['tbl3', 'tbl4'];
        $truncatedTables = ['tbl5', 'tbl6'];

        // Build the Mysqldump settings
        $config = $this->createConfigMock($dumpSettings, $includedTables, $excludedTables, $truncatedTables);
        $builder = new MysqldumpSettingsBuilder();
        $result = $builder->build($config);

        $this->assertSame(
            $this->getExpectedResult($dumpSettings, $includedTables, $excludedTables, $truncatedTables),
            $result
        );
    }

    /**
     * Test the builder when only a few settings are defined.
     */
    public function testPartialSettings(): void
    {
        $dumpSettings = [
            'compress' => 'gzip',
            'hex_blob' => true,
            'net_buffer_length' => 1000000,
            'skip_definer' => true,
            'skip_dump_date' => false,
        ];

        $includedTables = ['tbl1', 'tbl2'];

        // Build the Mysqldump settings
        $config = $this->createConfigMock($dumpSettings, $includedTables);
        $builder = new MysqldumpSettingsBuilder();
        $result = $builder->build($config);

        $this->assertSame($this->getExpectedResult($dumpSettings, $includedTables), $result);
    }

    /**
     * Test the builder with empty dump settings.
     */
    public function testEmptySettings(): void
    {
        $config = $this->createConfigMock();
        $builder = new MysqldumpSettingsBuilder();
        $result = $builder->build($config);

        $this->assertSame($this->getExpectedResult(), $result);
    }

    /**
     * Assert that an exception is thrown when the dump settings contain an unallowed parameter.
     */
    public function testUndefinedSettings(): void
    {
        $config = $this->createConfigMock(['not_exists' => true]);
        $builder = new MysqldumpSettingsBuilder();

        $this->expectException(RuntimeException::class);
        $builder->build($config);
    }

    /**
     * Get the expected value returned by the builder.
     */
    private function getExpectedResult(
        array $dumpSettings = [],
        array $includedTables = [],
        array $excludedTables = [],
        array $truncatedTables = [],
    ): array {
        $result = $dumpSettings;

        foreach ($result as $key => $value) {
            // Keys with "_" were replaced with "-"
            if (str_contains($key, '_') && $key !== 'init_commands' && $key !== 'net_buffer_length') {
                $result[str_replace('_', '-', $key)] = $value;
                unset($result[$key]);
            }
        }

        // Compress value starts with an uppercase letter
        if (array_key_exists('compress', $result)) {
            $result['compress'] = lcfirst($result['compress']);
        }

        $result['include-tables'] = $includedTables;
        $result['exclude-tables'] = $excludedTables;
        $result['no-data'] = $truncatedTables;

        // A readonly session init command was added
        $result['init_commands'][] = 'SET SESSION TRANSACTION READ ONLY';

        return $result;
    }

    /**
     * Create a dumper config object.
     */
    private function createConfigMock(
        array $dumpSettings = [],
        array $includedTables = [],
        array $excludedTables = [],
        array $truncatedTables = [],
    ): DumperConfigInterface {
        $configMock = $this->createMock(DumperConfigInterface::class);

        $configMock
            ->method('getDumpSettings')
            ->willReturn($dumpSettings);

        $configMock
            ->method('getIncludedTables')
            ->willReturn($includedTables);

        $configMock
            ->method('getExcludedTables')
            ->willReturn($excludedTables);

        $configMock
            ->method('getTablesToTruncate')
            ->willReturn($truncatedTables);

        return $configMock;
    }
}
