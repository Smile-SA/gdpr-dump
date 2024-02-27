<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Event;

use Smile\GdprDump\Database\Database;
use Smile\GdprDump\Dumper\Config\DumperConfig;
use Spatie\DbDumper\DbDumper;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched before the dump creation.
 */
class DumpEvent extends Event
{
    public function __construct(
        private readonly DbDumper $dumper,
        private readonly Database $database,
        private readonly DumperConfig $config,
        private readonly array $context
    ) {
    }

    /**
     * Get the dumper config.
     */
    public function getConfig(): DumperConfig
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
     * Get the dump context.
     */
    public function getDumper(): DbDumper
    {
        return $this->dumper;
    }

    /**
     * Get the context.
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
