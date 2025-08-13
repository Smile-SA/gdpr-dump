<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\EventListener;

use Smile\GdprDump\Config\Event\ConfigLoadedEvent;
use Smile\GdprDump\Config\Helper\TableNamesProcessor;
use Smile\GdprDump\Database\Metadata\DatabaseMetadata;
use Smile\GdprDump\Dumper\Event\DatabaseConnectedEvent;
use Smile\GdprDump\Dumper\Event\DumpTerminated;

final class TableNamesListener
{
    private DatabaseMetadata $metadata;

    /**
     * Get the database metadata object when the connection is established.
     */
    public function onDatabaseConnected(DatabaseConnectedEvent $event)
    {
        $this->metadata = $event->getDatabase()->getMetadata();
    }

    /**
     * If the database connection is established, resolve all tables names (e.g. `log_*`).
     *
     * This requires the config to be loaded twice (1st load, read the db credentials, open the db, 2nd load).
     */
    public function onConfigLoaded(ConfigLoadedEvent $event): void
    {
        if (!isset($this->metadata)) {
            return;
        }

        $config = $event->getConfigData();

        (new TableNamesProcessor($this->metadata))->process($config);
    }

    /**
     * Reset the listener state.
     */
    public function onDumpTermination(DumpTerminated $event): void
    {
        var_dump('unset metadata');
        unset($this->metadata);
    }
}
