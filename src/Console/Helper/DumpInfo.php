<?php

declare(strict_types=1);

namespace Smile\GdprDump\Console\Helper;

use Generator;
use Smile\GdprDump\Configuration\Configuration;
use Smile\GdprDump\Database\Database;
use Smile\GdprDump\Database\Metadata\DatabaseMetadata;
use Smile\GdprDump\Dumper\Event\DumpEvent;
use Smile\GdprDump\Dumper\Event\DumpFinishedEvent;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class DumpInfo
{
    private ProgressBar $progressBar;
    private ?array $lastTableInfo = null;

    public function __construct(private OutputInterface $output, private EventDispatcherInterface $eventDispatcher)
    {
        $this->progressBar = new ProgressBar($output);
        $this->progressBar->minSecondsBetweenRedraws(0.1); // default: 0.04
    }

    /**
     * Register the event listeners.
     */
    public function addListeners(): void
    {
        $this->eventDispatcher->addListener(DumpEvent::class, [$this, 'onDumpStarted']);
        $this->eventDispatcher->addListener(DumpFinishedEvent::class, [$this, 'onDumpFinished']);
    }

    /**
     * Unregister the event listeners.
     */
    public function removeListeners(): void
    {
        $this->eventDispatcher->removeListener(DumpEvent::class, [$this, 'onDumpStarted']);
        $this->eventDispatcher->removeListener(DumpFinishedEvent::class, [$this, 'onDumpFinished']);
    }

    /**
     * Get the listener that will be triggered when a dump is started.
     */
    public function onDumpStarted(DumpEvent $event): void
    {
        $configuration = $event->getConfiguration();
        $database = $event->getDatabase();

        $this->displayDatabaseSettings($database);
        $this->displayDumpSettings($configuration);
        $this->displayTablesInfo($configuration);
        $this->displayProgressBar($this->getMaxSteps($configuration, $database->getMetadata()));

        // Set the hook that updates the progress bar during the dump creation
        $event->getDumper()->setInfoHook($this->getDumpInfoHook());
    }

    /**
     * Display database settings.
     */
    private function displayDatabaseSettings(Database $database): void
    {
        $this->displaySection('Database Settings');
        $this->displaySectionItem('Dsn', $database->getDriver()->getDsn());
        $this->displaySectionItem(
            'Using password',
            $database->getConnectionParams()->get('password') ? 'yes' : 'no'
        );
    }

    /**
     * Display dump settings.
     */
    private function displayDumpSettings(Configuration $configuration): void
    {
        $this->output->writeln('');
        $this->displaySection('Dump Settings');

        foreach ($this->getDumpSettings($configuration) as $name => $value) {
            $this->displaySectionItem($name, $value);
        }
    }

    /**
     * Display information about the tables to dump.
     */
    private function displayTablesInfo(Configuration $configuration): void
    {
        if (!$configuration->getIncludedTables() && !$configuration->getExcludedTables()) {
            return;
        }

        $this->output->writeln('');
        $this->displaySection('Tables');

        if ($configuration->getIncludedTables()) {
            $this->displaySectionItem('Included', $configuration->getIncludedTables());
        }

        if ($configuration->getExcludedTables()) {
            $this->displaySectionItem('Excluded', $configuration->getExcludedTables());
        }
    }

    /**
     * Display the progress bar.
     */
    private function displayProgressBar(int $maxSteps): void
    {
        // Dump progress bar
        $this->output->writeln('');
        $this->displaySection('Dump progress');

        // Configure and start the progress bar
        $this->progressBar->setFormat(
            ' %current%/%max% [%bar%] %percent:3s%% - %title% - %elapsed:6s% - %memory:6s%'
        );
        $this->progressBar->setMaxSteps($maxSteps);
        $this->progressBar->setMessage('<info>Starting dump</info>', 'title');
        $this->progressBar->start();
    }

    /**
     * Get the listener that will be triggered when a dump is finished.
     */
    public function onDumpFinished(): void
    {
        if ($this->lastTableInfo) {
            // Display information of the last table that was dumped
            $this->updateProgressBarMessage($this->lastTableInfo);
        }

        $this->progressBar->finish();
        $this->output->writeln('');
    }

    /**
     * Get the hook that will be triggered when an object is being dumped.
     */
    private function getDumpInfoHook(): callable
    {
        return function (string $object, array $info): void {
            if ($object !== 'table') {
                // The max steps of the progress bar only include tables
                return;
            }

            if ($info['completed']) {
                $this->lastTableInfo = $info;
                return;
            }

            $this->updateProgressBarMessage($info);

            $info['rowCount'] === 0
                ? $this->progressBar->advance() // new table
                : $this->progressBar->setProgress($this->progressBar->getProgress()); // refresh current table progress
        };
    }

    /**
     * Display a section.
     */
    private function displaySection(string $name): void
    {
        $this->output->writeln(sprintf('<comment>%s</comment>', $name));
    }

    /**
     * Display a section item.
     */
    private function displaySectionItem(string $name, mixed $value): void
    {
        $this->output->writeln(sprintf('%s: <info>%s</info>', $name, $this->formatValue($value)));
    }

    /**
     * Update the progress bar message.
     */
    private function updateProgressBarMessage(array $tableInfo): void
    {
        $message = sprintf('<info>%s</info> (%s)', $tableInfo['name'], $this->formatRowCount($tableInfo['rowCount']));
        $this->progressBar->setMessage($message, 'title');
    }

    /**
     * Get max number of steps of the progress bar.
     */
    private function getMaxSteps(Configuration $configuration, DatabaseMetadata $metadata): int
    {
        $includedTables = $configuration->getIncludedTables() ?: $metadata->getTableNames();
        $excludedTables = $configuration->getExcludedTables();

        return count(array_diff($includedTables, $excludedTables));
    }

    /**
     * Format a value for console output.
     */
    private function formatValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Format the row count.
     */
    private function formatRowCount(int $rowCount): string
    {
        $formatted = number_format($rowCount, 0, '.', ' ');

        return $rowCount > 1 ? $formatted . ' rows' : $formatted . ' row';
    }

    /**
     * Get an array representation of the dump settings.
     */
    private function getDumpSettings(Configuration $configuration): Generator
    {
        $dumpConfig = $configuration->getDumpSettings();

        yield 'output' => $dumpConfig->getOutput();
        yield 'add_drop_database' => $dumpConfig->getAddDropDatabase();
        yield 'add_drop_table' => $dumpConfig->getAddDropTable();
        yield 'add_drop_trigger' => $dumpConfig->getAddDropTrigger();
        yield 'add_locks' => $dumpConfig->getAddLocks();
        yield 'complete_insert' => $dumpConfig->getCompleteInsert();
        yield 'compress' => $dumpConfig->getCompress();
        yield 'default_character_set' => $dumpConfig->getDefaultCharacterSet();
        yield 'disable_keys' => $dumpConfig->getDisableKeys();
        yield 'events' => $dumpConfig->getEvents();
        yield 'extended_insert' => $dumpConfig->getExtendedInsert();
        yield 'hex_blob' => $dumpConfig->getHexBlob();
        yield 'insert_ignore' => $dumpConfig->getInsertIgnore();
        yield 'lock_tables' => $dumpConfig->getLockTables();
        yield 'net_buffer_length' => $dumpConfig->getNetBufferLength();
        yield 'no_autocommit' => $dumpConfig->getNoAutocommit();
        yield 'no_create_info' => $dumpConfig->getNoCreateInfo();
        yield 'routines' => $dumpConfig->getRoutines();
        yield 'single_transaction' => $dumpConfig->getSingleTransaction();
        yield 'skip_comments' => $dumpConfig->getSkipComments();
        yield 'skip_definer' => $dumpConfig->getSkipDefiner();
        yield 'skip_dump_date' => $dumpConfig->getSkipDumpDate();
        yield 'skip_triggers' => $dumpConfig->getSkipTriggers();
        yield 'skip_tz_utc' => $dumpConfig->getSkipTzUtc();
    }
}
