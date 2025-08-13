<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Loader\Resource;

class ResourceFactory
{
    public function __construct(private ResourceLocator $resourceLocator)
    {
    }

    /**
     * Create a file resource.
     */
    public function createFileResource(string $fileName, ?string $currentDirectory = null): Resource
    {
        $fileName = $this->resourceLocator->locate($fileName, $currentDirectory);

        return new Resource($fileName);
    }

    /**
     * Create a string resource.
     */
    public function createStringResource(string $input): Resource
    {
        return new Resource($input, false);
    }
}
