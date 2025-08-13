<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Loader;

use Smile\GdprDump\Config\Resource\Resource;

interface Loader
{
    /**
     * Add a resource to the loader.
     */
    public function addResource(Resource $resource): self;

    /**
     * Load the configuration from registered resources.
     *
     * @throws ConfigLoadException
     */
    public function load(): object;
}
