<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Event;

use Smile\GdprDump\Dumper\Config\DumperConfig;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched after a dump creation.
 */
class DumpFinishedEvent extends Event
{
    public function __construct(private DumperConfig $config)
    {
    }

    /**
     * Get the dumper config.
     */
    public function getConfig(): DumperConfig
    {
        return $this->config;
    }
}
