<?php

declare(strict_types=1);

namespace Smile\GdprDump\Console\Helper;

use Smile\GdprDump\Database\Metadata\MetadataInterface;
use Smile\GdprDump\Dumper\Config\DumperConfig;
use Smile\GdprDump\Dumper\Event\DumpEvent;
use Smile\GdprDump\Dumper\Event\DumpFinishedEvent;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DumpInfo
{
    private OutputInterface $output;
    private ProgressBar $progressBar;
    private ?array $lastTableInfo = null;

    public function __construct(private EventDispatcherInterface $eventDispatcher)
    {
    }

    /**
     * Set the output that will display the dump progress.
     */
    public function setOutput(OutputInterface $output): void
    {
        if ($output instanceof ConsoleOutputInterface) {
            $output = $output->getErrorOutput();
        }

        $this->output = $output;
        $this->progressBar = new ProgressBar($output);
        $this->progressBar->minSecondsBetweenRedraws(0.1); // default: 0.04
        $this->eventDispatcher->addListener(DumpEvent::class, $this->getDumpStartedListener());
        $this->eventDispatcher->addListener(DumpFinishedEvent::class, $this->getDumpFinishedListener());
    }

    /**
     * Get the listener that will be triggered when a dump is started.
     */
    private function getDumpStartedListener(): callable
    {
        return function (DumpEvent $event): void {
            $config = $event->getConfig();
            $database = $event->getDatabase();

            // DSN
            $this->displaySection('Database settings');
            $this->displaySectionItem('Dsn', $database->getDriver()->getDsn());
            $this->displaySectionItem(
                'Using password',
                $database->getConnectionParams()->get('password') ? 'yes' : 'no'
            );

            // Dump settings
            $this->output->writeln('');
            $this->displaySection('Dump settings');
            foreach ($config->getDumpSettings() as $name => $value) {
                $this->displaySectionItem($name, $value);
            }

            $this->output->writeln('');
            $this->displaySection('Dump progress');

            // Configure and start the progress bar
            $this->progressBar->setFormat(
                ' %current%/%max% [%bar%] %percent:3s%% - %title% - %elapsed:6s% - %memory:6s%'
            );
            $this->progressBar->setMaxSteps($this->getMaxSteps($config, $database->getMetadata()));
            $this->progressBar->setMessage('<info>Starting dump</info>', 'title');
            $this->progressBar->start();

            // Set the hook that will update the bar during the dump creation
            $event->getDumper()->setInfoHook($this->getDumpInfoHook());
        };
    }

    /**
     * Get the listener that will be triggered when a dump is finished.
     */
    private function getDumpFinishedListener(): callable
    {
        return function (): void {
            if ($this->lastTableInfo) {
                // Display information of the last table that was dumped
                $this->updateProgressBarMessage($this->lastTableInfo);
            }

            $this->progressBar->finish();
            $this->output->writeln('');
        };
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
        $this->output->writeln(sprintf('  - %s: <info>%s</info>', $name, $this->formatValue($value)));
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
    private function getMaxSteps(DumperConfig $config, MetadataInterface $metadata): int
    {
        $includedTables = $config->getTablesWhitelist() ?: $metadata->getTableNames();
        $excludedTables = $config->getTablesBlacklist();

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
}
