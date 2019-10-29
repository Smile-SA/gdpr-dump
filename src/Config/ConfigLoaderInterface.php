<?php
declare(strict_types=1);

namespace Smile\GdprDump\Config;

use Smile\GdprDump\Config\Parser\ParseException;
use Smile\GdprDump\Config\Resolver\FileNotFoundException;

interface ConfigLoaderInterface
{
    /**
     * Load a config file and merge its data to the config storage.
     *
     * @param string $fileName
     * @return $this
     * @throws FileNotFoundException
     * @throws ParseException
     */
    public function loadFile(string $fileName): ConfigLoaderInterface;

    /**
     * Load version-specific configuration.
     *
     * @return $this
     * @throws ParseException
     */
    public function loadVersionData(): ConfigLoaderInterface;
}
