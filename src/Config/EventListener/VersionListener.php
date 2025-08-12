<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\EventListener;

use Smile\GdprDump\Config\ConfigInterface;
use Smile\GdprDump\Config\Event\LoadEvent;
use Smile\GdprDump\Config\Event\MergeEvent;
use Smile\GdprDump\Config\Event\ParseEvent;
use Smile\GdprDump\Config\Validator\ValidationException;
use Smile\GdprDump\Config\Version\MissingVersionException;
use Smile\GdprDump\Config\Version\VersionMatcher;

final class VersionListener
{
    private string $version = '';

    /**
     * Reset the application version when the config loader starts the loading process.
     */
    public function onLoad(LoadEvent $event): void
    {
        $config = $event->getConfig();

        // Check if the version is already provided in the initial configuration
        $this->version = $this->getVersion($config);
    }

    /**
     * Detect the application version from the parsed file.
     */
    public function onParse(ParseEvent $event): void
    {
        $config = $event->getConfig();

        if ($this->version === '') {
            $this->version = $this->getVersion($config);
        }
    }

    /**
     * Merge the contents of `if_version` blocks to the configuration.
     */
    public function onMerge(MergeEvent $event): void
    {
        $config = $event->getConfig();
        $versionsData = $this->getVersionsData($config);
        if (!$versionsData) {
            return;
        }

        if ($this->version === '') {
            throw new MissingVersionException('The application version must be specified in the configuration.');
        }

        $versionMatcher = new VersionMatcher();

        // Merge version-specific data into the configuration
        foreach ($versionsData as $requirement => $versionData) {
            if (!is_string($requirement)) {
                throw new ValidationException('Could not parse the version requirement.');
            }

            if (!is_array($versionData)) {
                throw new ValidationException(
                    sprintf('Could not parse data for version requirement "%s".', $requirement)
                );
            }

            if ($versionMatcher->match($requirement, $this->version)) {
                $config->merge($versionData)
                    ->remove('if_version');
            }
        }
    }

    /**
     * Get the application version.
     */
    private function getVersion(ConfigInterface $config): string
    {
        $version = $config->get('version', '');
        if (!is_string($version)) {
            throw new ValidationException('The parameter "version" must be a string.');
        }

        return $version;
    }

    /**
     * Get the version-specific data.
     */
    private function getVersionsData(ConfigInterface $config): array
    {
        $versionsData = $config->get('if_version', []);
        if (!is_array($versionsData)) {
            throw new ValidationException('The parameter "if_version" must be an object.');
        }

        return $versionsData;
    }
}
