<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Event;

use stdClass;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched when a resource is parsed.
 */
final class ParseResourceEvent extends Event
{
    public function __construct(
        private stdClass $configurationData,
        private stdClass $resourceData,
        private bool $first
    ) {
    }

    /**
     * Get the data that was already parsed from previous files.
     */
    public function getConfigurationData(): stdClass
    {
        return $this->configurationData;
    }

    /**
     * Get the data that was parsed from the resource.
     */
    public function getResourceData(): stdClass
    {
        return $this->resourceData;
    }

    /**
     * Check if the current resource is the first that was parsed.
     */
    public function isFirstParse(): bool
    {
        return $this->first;
    }
}
