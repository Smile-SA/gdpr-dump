<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\EventListener;

use Smile\GdprDump\Config\Event\ParseConfigEvent;
use Smile\GdprDump\Database\Driver\DriverType;
use Smile\GdprDump\Util\Objects;

final class DefaultSettingsListener
{
    /**
     * Add default settings to the configuration.
     */
    public function __invoke(ParseConfigEvent $event): void
    {
        return; // TODO which event??? will be useles anyway with a DumpConfig object
        $config = $event->getConfigData();

        foreach ($this->getDefaultSettings() as $property => $value) {
            if (!property_exists($config, $property)) {
                $config->$property = $value;
                continue;
            }

            if (is_object($value)) {
                Objects::merge($value, $config->$property);
                $config->$property = $value;
            }
        }
    }

    /**
     * Get default settings.
     */
    private function getDefaultSettings(): object
    {
        return (object) [
            'database' => (object) [
                'driver' => Drivers::MYSQL,
            ],
            'dump' => (object) [
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
            'faker' => (object) [
                'locale' => '',
            ],
            'filter_propagation' => (object) [
                'enabled' => true,
                'ignored_foreign_keys' => [],
            ],
            'tables' => (object) [],
            'tables_whitelist' => [],
            'tables_blacklist' => [],
            'variables' => (object) [],
        ];
    }
}
