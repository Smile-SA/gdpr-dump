<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Event;

use stdClass;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched when the configuration data is mapped to the DumperConfig object.
 */
final class MapConfigEvent extends Event
{
    public function __construct(private object $config)
    {
    }

    /**
     * Get the data that will be mapped to the configuration object.
     */
    public function getConfigData(): stdClass
    {
        return $this->config;
    }
}
