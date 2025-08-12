<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Event;

use Smile\GdprDump\Config\ConfigInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched when a config file is parsed.
 */
final class ParseEvent extends Event
{
    public function __construct(private ConfigInterface $config)
    {
    }

    /**
     * Get the configuration that was parsed from the file.
     */
    public function getConfig(): ConfigInterface
    {
        return $this->config;
    }
}
