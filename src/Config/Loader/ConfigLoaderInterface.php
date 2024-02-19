<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Loader;

use Smile\GdprDump\Config\ConfigException;
use Smile\GdprDump\Config\ConfigInterface;

interface ConfigLoaderInterface
{
    /**
     * Load a config file and merge its data to the config storage.
     *
     * @throws ConfigException
     */
    public function load(string $fileName, ConfigInterface $config): void;
}
