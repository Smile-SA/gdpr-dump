<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Loader\Locator;

use Smile\GdprDump\Config\Exception\FileLocatorException;

interface FileLocator
{
    /**
     * Resolves the absolute path of a config template.
     *
     * The $path variable can be a template name (e.g. "magento2") or a relative/absolute path.
     *
     * If the $currentDirectory variable is specified, it will be used as the current working directory
     * to search for relative paths.
     *
     * @throws FileLocatorException
     */
    public function locate(string $path, ?string $currentDirectory = null): string;
}
