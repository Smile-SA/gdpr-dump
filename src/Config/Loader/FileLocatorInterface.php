<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Loader;

interface FileLocatorInterface
{
    /**
     * Resolve a path (either relative or absolute).
     *
     * @throws FileNotFoundException
     */
    public function locate(string $path, ?string $currentDirectory = null): string;
}
