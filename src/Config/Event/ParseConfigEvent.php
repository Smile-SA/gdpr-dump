<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Event;

use stdClass;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched when a configuration file is parsed.
 */
final class ParseConfigEvent extends Event
{
    public function __construct(private object $config)
    {
    }

    /**
     * Get the data that was parsed from the file.
     */
    public function getConfigData(): stdClass
    {
        return $this->config;
    }
}
