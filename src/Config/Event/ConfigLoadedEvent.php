<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Event;

use stdClass;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched when the configuration was loaded.
 */
final class ConfigLoadedEvent extends Event
{
    public function __construct(private object $config)
    {
    }

    /**
     * Get the loaded configuration data.
     */
    public function getConfigData(): stdClass
    {
        return $this->config;
    }
}
