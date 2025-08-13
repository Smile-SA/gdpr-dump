<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched when the configuration loader starts loading the configuration.
 */
final class ConfigLoadEvent extends Event
{
}
