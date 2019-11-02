<?php
declare(strict_types=1);

namespace Smile\GdprDump\Config\Version;

use Smile\GdprDump\Config\ConfigInterface;

interface VersionLoaderInterface
{
    /**
     * Load version-specific config data.
     *
     * @param ConfigInterface $config
     * @throws InvalidVersionException
     * @throws MissingVersionException
     */
    public function load(ConfigInterface $config);
}
