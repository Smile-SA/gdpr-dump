<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Event;

use Smile\GdprDump\Configuration\Configuration;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched after a dump creation.
 */
final class DumpFinishedEvent extends Event
{
    public function __construct(private Configuration $configuration)
    {
    }

    /**
     * Get the dumper configuration.
     */
    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }
}
