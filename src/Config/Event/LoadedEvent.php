<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Event;

use Smile\GdprDump\Config\ConfigInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched when the configuration was loaded.
 */
final class LoadedEvent extends Event
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
