<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Event;

use Smile\GdprDump\Database\ConnectionProvider;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched before the dump creation.
 */
final class DatabaseConnectedEvent extends Event
{
    public function __construct(private ConnectionProvider $database)
    {
    }

    // TODO KEEP?

    /**
     * Get the database wrapper.
     */
    public function getDatabase(): ConnectionProvider
    {
        return $this->database;
    }
}
