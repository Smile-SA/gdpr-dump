<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Event;

use stdClass;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched when the configuration was successfully parsed.
 */
final class ConfigParsedEvent extends Event
{
    public function __construct(private stdClass $configurationData)
    {
    }

    /**
     * Get the parsed configuration data.
     */
    public function getConfigurationData(): stdClass
    {
        return $this->configurationData;
    }
}
