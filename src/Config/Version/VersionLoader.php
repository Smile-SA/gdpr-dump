<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Version;

use Smile\GdprDump\Config\ConfigInterface;

class VersionLoader implements VersionLoaderInterface
{
    /**
     * @inheritdoc
     */
    public function load(ConfigInterface $config)
    {
        $requiresVersion = (bool) $config->get('requires_version');
        $version = (string) $config->get('version');
        $versionsData = (array) $config->get('if_version');

        if ($version === '') {
            // Check if version is mandatory
            if ($requiresVersion) {
                // phpcs:ignore Generic.Files.LineLength.TooLong
                throw new MissingVersionException('The application version must be specified in the configuration.');
            }
            return;
        }

        if (empty($versionsData)) {
            return;
        }

        $versionMatcher = new VersionMatcher();

        // Merge version-specific data into the configuration
        foreach ($versionsData as $requirement => $versionData) {
            if ($versionMatcher->match($requirement, $version)) {
                $config->merge($versionData);
            }
        }
    }
}
