<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Compiler\Processor;

use Smile\GdprDump\Config\Compiler\Processor\DefaultSettingsProcessor;
use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Tests\Unit\TestCase;

final class DefaultSettingsProcessorTest extends TestCase
{
    /**
     * Test default values added by the processor.
     */
    public function testDefaultValues(): void
    {
        $config = new Config();
        $processor = new DefaultSettingsProcessor();
        $processor->process($config);

        $this->assertSame([], $config->get('database'));
        $this->assertSame([], $config->get('tables_blacklist'));
        $this->assertSame([], $config->get('tables_whitelist'));
        $this->assertSame([], $config->get('tables'));
        $this->assertSame([], $config->get('variables'));

        $fakerSettings = $config->get('faker');
        $this->assertIsArray($fakerSettings);
        $this->assertArrayHasKey('locale', $fakerSettings);
        $this->assertSame('', $fakerSettings['locale']);

        $filterPropagationSettings = $config->get('filter_propagation');
        $this->assertIsArray($filterPropagationSettings);
        $this->assertArrayHasKey('enabled', $filterPropagationSettings);
        $this->assertTrue($filterPropagationSettings['enabled']);
        $this->assertArrayHasKey('ignored_foreign_keys', $filterPropagationSettings);
        $this->assertSame([], $filterPropagationSettings['ignored_foreign_keys']);

        $dumpSettings = $config->get('dump');
        $this->assertIsArray($dumpSettings);
        $this->assertArrayHasKey('output', $dumpSettings);
        $this->assertArrayHasKey('add_drop_database', $dumpSettings);
        $this->assertArrayHasKey('add_drop_table', $dumpSettings);
        $this->assertArrayHasKey('add_drop_trigger', $dumpSettings);
        $this->assertArrayHasKey('add_locks', $dumpSettings);
        $this->assertArrayHasKey('complete_insert', $dumpSettings);
        $this->assertArrayHasKey('compress', $dumpSettings);
        $this->assertArrayHasKey('default_character_set', $dumpSettings);
        $this->assertArrayHasKey('disable_keys', $dumpSettings);
        $this->assertArrayHasKey('events', $dumpSettings);
        $this->assertArrayHasKey('extended_insert', $dumpSettings);
        $this->assertArrayHasKey('hex_blob', $dumpSettings);
        $this->assertArrayHasKey('init_commands', $dumpSettings);
        $this->assertArrayHasKey('insert_ignore', $dumpSettings);
        $this->assertArrayHasKey('lock_tables', $dumpSettings);
        $this->assertArrayHasKey('net_buffer_length', $dumpSettings);
        $this->assertArrayHasKey('no_autocommit', $dumpSettings);
        $this->assertArrayHasKey('no_create_info', $dumpSettings);
        $this->assertArrayHasKey('routines', $dumpSettings);
        $this->assertArrayHasKey('single_transaction', $dumpSettings);
        $this->assertArrayHasKey('skip_comments', $dumpSettings);
        $this->assertArrayHasKey('skip_definer', $dumpSettings);
        $this->assertArrayHasKey('skip_dump_date', $dumpSettings);
        $this->assertArrayHasKey('skip_triggers', $dumpSettings);
        $this->assertArrayHasKey('skip_tz_utc', $dumpSettings);
    }

    /**
     * Assert that default values are existing merged with an existing config.
     */
    public function testMergeValues(): void
    {
        $data = [
            'dump' => ['output' => 'dump.sql'],
            'faker' => [
                'locale' => 'en_US',
            ],
            'filter_propagation' => [
                'ignored_foreign_keys' => ['fk1', 'fk2'],
            ],
            'tables_blacklist' => ['table1'],
        ];

        $config = new Config($data);
        $processor = new DefaultSettingsProcessor();
        $processor->process($config);

        $dumpSettings = $config->get('dump');
        $this->assertIsArray($dumpSettings);
        $this->assertArrayHasKey('output', $dumpSettings);
        $this->assertSame('dump.sql', $dumpSettings['output']);

        $fakerSettings = $config->get('faker');
        $this->assertIsArray($fakerSettings);
        $this->assertArrayHasKey('locale', $fakerSettings);
        $this->assertSame('en_US', $fakerSettings['locale']);

        $filterPropagationSettings = $config->get('filter_propagation');
        $this->assertIsArray($filterPropagationSettings);
        $this->assertArrayHasKey('enabled', $filterPropagationSettings);
        $this->assertTrue($filterPropagationSettings['enabled']);
        $this->assertArrayHasKey('ignored_foreign_keys', $filterPropagationSettings);
        $this->assertSame(
            $data['filter_propagation']['ignored_foreign_keys'],
            $filterPropagationSettings['ignored_foreign_keys']
        );
    }
}
