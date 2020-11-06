<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Loader;

use Smile\GdprDump\Config\ConfigException;

interface ConfigLoaderInterface
{
    /**
     * Load a config file and merge its data to the config storage.
     *
     * @param string $fileName
     * @throws ConfigException
     */
    public function load(string $fileName): void;
}
