<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Builder;

use Smile\GdprDump\Dumper\Config\DumperConfigInterface;
use Smile\GdprDump\Util\ArrayHelper;

final class MysqldumpSettingsBuilder
{
    public function __construct(private ArrayHelper $arrayHelper)
    {
    }

    /**
     * Build mysqldump-php settings.
     */
    public function build(DumperConfigInterface $config): array
    {
        $settings = $config->getDumpSettings();
        $settings = $this->arrayHelper->map($settings, $this->getMapping());

        // "compress" setting must start with an uppercase letter (e.g. "gzip" -> "Gzip")
        if (array_key_exists('compress', $settings)) {
            $settings['compress'] = lcfirst($settings['compress']);
        }

        // Tables to include/exclude/truncate
        $settings['include-tables'] = $config->getIncludedTables();
        $settings['exclude-tables'] = $config->getExcludedTables();
        $settings['no-data'] = $config->getTablesToTruncate();

        // Set readonly session
        $settings['init_commands'][] = 'SET SESSION TRANSACTION READ ONLY';

        return $settings;
    }

    /**
     * Get the mapping between GdprDump settings and mysqldump-php settings.
     */
    private function getMapping(): array
    {
        return [
            // This list must match the dump object defined in schema.json
            // (except "output" which is stored elsewhere in the DumperConfig object)
            'add_drop_database' => 'add-drop-database',
            'add_drop_table' => 'add-drop-table',
            'add_drop_trigger' => 'add-drop-trigger',
            'add_locks' => 'add-locks',
            'complete_insert' => 'complete-insert',
            'compress' => 'compress',
            'default_character_set' => 'default-character-set',
            'disable_keys' => 'disable-keys',
            'events' => 'events',
            'extended_insert' => 'extended-insert',
            'hex_blob' => 'hex-blob',
            'init_commands' => 'init_commands',
            'insert_ignore' => 'insert-ignore',
            'lock_tables' => 'lock-tables',
            'net_buffer_length' => 'net_buffer_length',
            'no_autocommit' => 'no-autocommit',
            'no_create_info' => 'no-create-info',
            'routines' => 'routines',
            'single_transaction' => 'single-transaction',
            'skip_comments' => 'skip-comments',
            'skip_definer' => 'skip-definer',
            'skip_dump_date' => 'skip-dump-date',
            'skip_triggers' => 'skip-triggers',
            'skip_tz_utc' => 'skip-tz-utc',
        ];
    }
}
