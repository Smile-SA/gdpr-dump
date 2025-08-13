<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Event;

use Druidfi\Mysqldump\Mysqldump;
use Smile\GdprDump\Configuration\Configuration;
use Smile\GdprDump\Database\Database;
use Smile\GdprDump\Dumper\DumpContext;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched before the dump creation.
 */
final class DumpEvent extends Event
{
    public function __construct(
        private Mysqldump $dumper,
        private Database $database,
        private Configuration $configuration,
        private DumpContext $dumpContext,
    ) {
    }

    /**
     * Get the dumper config.
     */
    public function getConfig(): Configuration
    {
        return $this->configuration;
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
    public function getDumpContext(): DumpContext
    {
        return $this->dumpContext;
    }
}
