<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Event;

use stdClass;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched when the data from a configuration file is merged.
 */
final class MergeConfigEvent extends Event
{
    public function __construct(private object $config)
    {
    }

    /**
     * Get the file data that will be merged into the previously loaded data.
     */
    public function getConfigData(): stdClass
    {
        return $this->config;
    }
}
