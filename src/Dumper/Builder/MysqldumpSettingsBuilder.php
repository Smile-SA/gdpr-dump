<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Builder;

use Smile\GdprDump\Configuration\Configuration;

final class MysqldumpSettingsBuilder
{
    /**
     * Build connection settings.
     */
    public function build(Configuration $configuration): array
    {
        $dumpConfig = $configuration->getDumpSettings();

        $initCommands = $dumpConfig->getInitCommands();
        $initCommands[] = 'SET SESSION TRANSACTION READ ONLY';

        return [
            'add-drop-database' => $dumpConfig->getAddDropDatabase(),
            'add-drop-table' => $dumpConfig->getAddDropTable(),
            'add-drop-trigger' => $dumpConfig->getAddDropTrigger(),
            'add-locks' => $dumpConfig->getAddLocks(),
            'complete-insert' => $dumpConfig->getCompleteInsert(),
            'compress' => lcfirst($dumpConfig->getCompress()),
            'default-character-set' => $dumpConfig->getDefaultCharacterSet(),
            'disable-keys' => $dumpConfig->getDisableKeys(),
            'events' => $dumpConfig->getEvents(),
            'exclude-tables' => $configuration->getExcludedTables(),
            'extended-insert' => $dumpConfig->getExtendedInsert(),
            'hex-blob' => $dumpConfig->getHexBlob(),
            'include-tables' => $configuration->getIncludedTables(),
            'init_commands' => $initCommands,
            'insert-ignore' => $dumpConfig->getInsertIgnore(),
            'lock-tables' => $dumpConfig->getLockTables(),
            'net_buffer_length' => $dumpConfig->getNetBufferLength(),
            'no-autocommit' => $dumpConfig->getNoAutocommit(),
            'no-data' => $configuration->getTableConfigs()->getTablesToTruncate(),
            'no-create-info' => $dumpConfig->getNoCreateInfo(),
            'routines' => $dumpConfig->getRoutines(),
            'single-transaction' => $dumpConfig->getSingleTransaction(),
            'skip-comments' => $dumpConfig->getSkipComments(),
            'skip-definer' => $dumpConfig->getSkipDefiner(),
            'skip-dump-date' => $dumpConfig->getSkipDumpDate(),
            'skip-triggers' => $dumpConfig->getSkipTriggers(),
            'skip-tz-utc' => $dumpConfig->getSkipTzUtc(),
        ];
    }
}
