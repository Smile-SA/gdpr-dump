<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\EventListener;

use Smile\GdprDump\Config\Event\LoadEvent;

final class DefaultSettingsListener
{
    /**
     * Add default settings to the configuration.
     */
    public function __invoke(LoadEvent $event): void
    {
        $config = $event->getConfig();
        $data = $config->toArray();
        $data += [
            'database' => [],
            'dump' => [],
            'faker' => [],
            'filter_propagation' => [],
            'tables_whitelist' => [],
            'tables_blacklist' => [],
            'tables' => [],
            'variables' => [],
        ];

        foreach ($this->getDefaultSettings() as $key => $value) {
            $data[$key] += $value;
        }

        $config->reset($data);
    }

    /**
     * Get default settings.
     */
    private function getDefaultSettings(): array
    {
        return [
            'dump' => [
                'output' => 'php://stdout',
                'add_drop_database' => false,
                'add_drop_table' => true, // false in MySQLDump-PHP
                'add_drop_trigger' => true,
                'add_locks' => true,
                'complete_insert' => false,
                'compress' => 'none',
                'default_character_set' => 'utf8',
                'disable_keys' => true,
                'events' => false,
                'extended_insert' => true,
                'hex_blob' => false, // true in MySQLDump-PHP
                'init_commands' => [],
                'insert_ignore' => false,
                'lock_tables' => false, // true in MySQLDump-PHP
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
            ],
            'filter_propagation' => [
                'enabled' => true,
                'ignored_foreign_keys' => [],
            ],
            'faker' => [
                'locale' => '',
            ],
        ];
    }
}
