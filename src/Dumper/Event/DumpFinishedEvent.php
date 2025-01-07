<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Event;

use Smile\GdprDump\Dumper\Config\DumperConfigInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched after a dump creation.
 */
final class DumpFinishedEvent extends Event
{
    public function __construct(private DumperConfigInterface $config)
    {
    }

    /**
     * Get the dumper config.
     */
    public function getConfig(): DumperConfigInterface
    {
        return $this->config;
    }
}
