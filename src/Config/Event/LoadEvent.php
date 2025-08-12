<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Event;

use Smile\GdprDump\Config\ConfigInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched on configuration load.
 */
final class LoadEvent extends Event
{
    public function __construct(private ConfigInterface $config)
    {
    }

    /**
     * Get the configuration container.
     */
    public function getConfig(): ConfigInterface
    {
        return $this->config;
    }
}
