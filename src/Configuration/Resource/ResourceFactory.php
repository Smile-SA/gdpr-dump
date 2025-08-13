<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Resource;

use Smile\GdprDump\Configuration\Loader\FileLocator;

// TODO unit tests
class ResourceFactory
{
    public function __construct(private FileLocator $fileLocator)
    {
    }

    /**
     * Create a file resource object. File extension is automatically detected.
     */
    public function createFileResource(string $fileName, ?string $currentDirectory = null): Resource
    {
        $fileName = $this->fileLocator->locate($fileName, $currentDirectory);
        $type = pathinfo($fileName, PATHINFO_EXTENSION);

        return new Resource($fileName, $type);
    }

    /**
     * Create a JSON resource object.
     */
    public function createJsonResource(string $input): Resource
    {
        return new Resource($input, 'json', false);
    }
}
