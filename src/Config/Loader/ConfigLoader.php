<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Loader;

use Smile\GdprDump\Config\Parser\Enum\Format;

interface ConfigLoader
{
    /**
     * Add a resource to the loader.
     */
    public function addResource(string $resource, Format $format): self;

    /**
     * Load the configuration from registered resources.
     *
     * @throws ConfigLoadException
     */
    public function load(): object;
}
