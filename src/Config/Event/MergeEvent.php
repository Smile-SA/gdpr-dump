<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Event;

use Smile\GdprDump\Config\ConfigInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched when a config file is merged.
 */
final class MergeEvent extends Event
{
    public function __construct(private ConfigInterface $config)
    {
    }

    /**
     * Get the configuration that was loaded from the file and that will be merged into the main configuration object.
     */
    public function getConfig(): ConfigInterface
    {
        return $this->config;
    }
}
