<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Event;

use Druidfi\Mysqldump\Mysqldump;
use Smile\GdprDump\Database\Database;
use Smile\GdprDump\Dumper\Config\DumperConfigInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched before the dump creation.
 */
final class DumpEvent extends Event
{
    public function __construct(
        private Mysqldump $dumper,
        private Database $database,
        private DumperConfigInterface $config,
        private array $context
    ) {
    }

    /**
     * Get the dumper config.
     */
    public function getConfig(): DumperConfigInterface
    {
        return $this->config;
    }

    /**
     * Get the database wrapper.
     */
    public function getDatabase(): Database
    {
        return $this->database;
    }

    /**
     * Get the dumper.
     */
    public function getDumper(): Mysqldump
    {
        return $this->dumper;
    }

    /**
     * Get the dump context.
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
