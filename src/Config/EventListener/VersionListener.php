<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\EventListener;

use Smile\GdprDump\Config\Event\ConfigLoadEvent;
use Smile\GdprDump\Config\Event\MergeConfigEvent;
use Smile\GdprDump\Config\Event\ParseConfigEvent;
use Smile\GdprDump\Config\Exception\InvalidVersionException;
use Smile\GdprDump\Config\Loader\EnvVarProcessor;
use Smile\GdprDump\Config\Version\VersionMatcher;
use Smile\GdprDump\Util\Objects;

final class VersionListener
{
    private string $version = '';

    public function __construct(private EnvVarProcessor $envVarProcessor)
    {
    }

    /**
     * Reset the listener state.
     */
    public function onConfigLoad(ConfigLoadEvent $event): void
    {
        $this->version = '';
    }

    /**
     * Detect the application version from the first value found of the "version" parameter.
     */
    public function onConfigParse(ParseConfigEvent $event): void
    {
        $config = $event->getConfigData();

        if ($this->version === '') {
            $this->version = $this->getVersion($config);
        }
    }

    /**
     * Merge the contents of the `if_version` parameter.
     */
    public function onConfigMerge(MergeConfigEvent $event): void
    {
        $config = $event->getConfigData();
        $versionsData = $this->getVersionsData($config);
        if (!$versionsData) {
            return;
        }

        if ($this->version === '') {
            throw new InvalidVersionException('The application version must be specified in the configuration.');
        }

        $versionMatcher = new VersionMatcher();

        // Merge version-specific data into the configuration
        foreach ($versionsData as $requirement => $versionData) {
            if (!is_string($requirement)) {
                throw new InvalidVersionException('Could not parse the version requirement.');
            }

            if (!is_object($versionData)) {
                throw new InvalidVersionException(
                    sprintf('Could not parse data for version requirement "%s".', $requirement)
                );
            }

            if ($versionMatcher->match($requirement, $this->version)) {
                Objects::merge($config, $versionData);
            }
        }

        unset($config->if_version);
    }

    /**
     * Get the application version.
     */
    private function getVersion(object $config): string
    {
        $version = $config->version ?? '';
        if (!is_string($version)) {
            throw new InvalidVersionException('The parameter "version" must be a string.');
        }

        if ($version !== '') {
            $version = $this->envVarProcessor->process($version);
        }

        return $version;
    }

    /**
     * Get the version-specific data.
     */
    private function getVersionsData(object $config): array
    {
        if (!property_exists($config, 'if_version')) {
            return [];
        }

        if (!is_object($config->if_version)) {
            throw new InvalidVersionException('The parameter "if_version" must be an object.');
        }

        return (array) $config->if_version;
    }
}
