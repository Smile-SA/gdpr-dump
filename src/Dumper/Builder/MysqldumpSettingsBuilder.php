<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Builder;

use Smile\GdprDump\Dumper\Config\DumperConfigInterface;

class MysqldumpSettingsBuilder
{
    /**
     * Build the mysqldump-php settings.
     */
    public function build(DumperConfigInterface $config): array
    {
        $settings = $config->getDumpSettings();

        // MySQLDump-PHP uses the '-' word separator for most settings
        foreach ($settings as $key => $value) {
            if ($key !== 'init_commands' && $key !== 'net_buffer_length') {
                $newKey = str_replace('_', '-', $key);

                if ($newKey !== $key) {
                    $settings[$newKey] = $value;
                    unset($settings[$key]);
                }
            }
        }

        if (array_key_exists('compress', $settings)) {
            // e.g. "gzip" -> "Gzip"
            $settings['compress'] = strtoupper($settings['compress']);
        }

        // Tables to include/exclude/truncate
        $settings['include-tables'] = $config->getIncludedTables();
        $settings['exclude-tables'] = $config->getExcludedTables();
        $settings['no-data'] = $config->getTablesToTruncate();

        // Set readonly session
        $settings['init_commands'][] = 'SET SESSION TRANSACTION READ ONLY';

        return $settings;
    }
}
