<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\EventListener;

use Smile\GdprDump\Configuration\Event\ConfigParsedEvent;
use Smile\GdprDump\Configuration\Event\MergeResourceEvent;
use Smile\GdprDump\Configuration\Event\ParseResourceEvent;
use Smile\GdprDump\Configuration\Loader\EnvVarProcessor;
use Smile\GdprDump\Configuration\Version\VersionMatcher;
use Smile\GdprDump\Configuration\Exception\ParseException;
use Smile\GdprDump\Util\Objects;
use stdClass;

final class VersionListener
{
    public function __construct(private EnvVarProcessor $envVarProcessor)
    {
    }

    /**
     * Detect the application version.
     */
    public function onResourceParse(ParseResourceEvent $event): void
    {
        $configuration = $event->getConfigurationData();
        $parsed = $event->getResourceData();

        if (property_exists($configuration, 'version')) {
            return;
        }

        // Add the first version found to the configuration object, it will then be used during merge
        $version = $this->getVersion($parsed);
        if ($version !== null) {
            $configuration->version = $version;
        }
    }

    /**
     * Merge the contents of the `if_version` parameter.
     */
    public function onResourceMerge(MergeResourceEvent $event): void
    {
        $configuration = $event->getConfigurationData();
        $parsed = $event->getResourceData();

        $versionsData = $this->getVersionsData($parsed);
        if (!$versionsData) {
            return;
        }

        $version = $this->getVersion($configuration);
        if ($version === null) {
            throw new ParseException('The application version must be specified in the configuration.');
        }

        // Remove version parameters before merging
        unset($parsed->version);
        unset($parsed->if_version);
        unset($parsed->requires_version); // deprecated param

        // Merge version-specific data into the configuration
        $versionMatcher = new VersionMatcher();

        foreach ($versionsData as $requirement => $versionData) {
            if (!is_string($requirement)) {
                throw new ParseException('Could not parse the version requirement.');
            }

            if (!is_object($versionData)) {
                throw new ParseException(
                    sprintf('Could not parse data for version requirement "%s".', $requirement)
                );
            }

            $this->validateVersionData($versionData);

            if ($versionMatcher->match($requirement, $version)) {
                Objects::merge($parsed, $versionData);
            }
        }
    }

    /**
     * Remove detected version after all if_version blocks were merged.
     */
    public function onConfigParsed(ConfigParsedEvent $event): void
    {
        $configuration = $event->getConfigurationData();
        unset($configuration->version);
    }

    /**
     * Get the application version from the specified object.
     */
    private function getVersion(stdClass $data): ?string
    {
        if (!property_exists($data, 'version')) {
            return null;
        }

        if (!is_string($data->version) || $data->version === '') {
            throw new ParseException('The parameter "version" must be a non-empty string.');
        }

        return $this->envVarProcessor->process($data->version);
    }

    /**
     * Get the version-specific data from the specified object.
     */
    private function getVersionsData(stdClass $data): array
    {
        if (!property_exists($data, 'if_version')) {
            return [];
        }

        if (!is_object($data->if_version)) {
            throw new ParseException('The parameter "if_version" must be an object.');
        }

        return get_object_vars($data->if_version);
    }

    /**
     * Validate that the version data block contains only allowed properties.
     */
    private function validateVersionData(stdClass $versionData): void
    {
        $properties = array_keys(get_object_vars($versionData));
        if (!$properties) {
            return;
        }

        $disallowedProperties = ['version', 'if_version'];
        $found = array_intersect($properties, $disallowedProperties);

        if ($found) {
            throw new ParseException(
                sprintf('Unsupported parameters found in if_version block: "%s".', implode('", "', $found))
            );
        }
    }
}
