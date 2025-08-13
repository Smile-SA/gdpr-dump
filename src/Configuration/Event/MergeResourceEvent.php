<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Event;

use stdClass;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched when the data from a configuration file is merged.
 */
final class MergeResourceEvent extends Event
{
    public function __construct(
        private stdClass $configurationData,
        private stdClass $resourceData,
        private bool $last
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
     * Get the data that will be merged into the configuration object.
     */
     public function getResourceData(): stdClass
     {
         return $this->resourceData;
     }

     /**
      * Check if the current resource is the last to be merged.
      */
     public function isLastMerge(): bool
     {
         return $this->last;
     }
}
