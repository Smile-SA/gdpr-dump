<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Builder;

use Smile\GdprDump\Configuration\Configuration;
use Smile\GdprDump\Dumper\Builder\MysqldumpSettingsBuilder;
use Smile\GdprDump\Tests\Unit\TestCase;

final class MysqldumpSettingsBuilderTest extends TestCase
{
    /**
     * Test the builder when all dump settings are defined.
     */
    public function testAllSettings(): void
    {
        $configuration = (new Configuration())
            ->setIncludedTables(['table1'])
            ->setExcludedTables(['table2'])
            ->setSqlVariables(['foo' => 'bar']);

        $configuration->getDumpSettings()
            ->setOutput('dump.sql') // must be ignored by the builder
            ->setAddDropDatabase(false)
            ->setAddDropTable(true)
            ->setAddDropTrigger(true)
            ->setAddLocks(true)
            ->setCompleteInsert(false)
            ->setCompress('gzip')
            ->setDefaultCharacterSet('utf8')
            ->setDisableKeys(true)
            ->setEvents(true)
            ->setExtendedInsert(true)
            ->setHexBlob(true)
            ->setInitCommands(['SET foobar'])
            ->setInsertIgnore(false)
            ->setLockTables(false)
            ->setNetBufferLength(10000)
            ->setNoAutocommit(true)
            ->setNoCreateInfo(false)
            ->setSingleTransaction(true)
            ->setSkipComments(false)
            ->setSkipDefiner(true)
            ->setSkipDumpDate(true)
            ->setSkipTriggers(false)
            ->setSkipTzUtc(false);

        $result = (new MysqldumpSettingsBuilder())->build($configuration);
        $this->assertSame($this->getExpectedResult($configuration), $result);
    }

    /**
     * Test the builder when only a few dump settings are defined.
     */
    public function testPartialSettings(): void
    {
        $configuration = (new Configuration())
            ->setIncludedTables(['table1']);

        $configuration->getDumpSettings()
            ->setHexBlob(true);

        $result = (new MysqldumpSettingsBuilder())->build($configuration);
        $this->assertSame($this->getExpectedResult($configuration), $result);
    }

    /**
     * Test the builder with empty dump settings.
     */
    public function testEmptySettings(): void
    {
        $configuration = new Configuration();
        $result = (new MysqldumpSettingsBuilder())->build($configuration);
        $this->assertSame($this->getExpectedResult($configuration), $result);
    }

    /**
     * Get the expected build result.
     */
    private function getExpectedResult(Configuration $configuration): array
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
