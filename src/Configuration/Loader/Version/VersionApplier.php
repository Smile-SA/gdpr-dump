<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Loader\Version;

use Smile\GdprDump\Configuration\Exception\ParseException;
use Smile\GdprDump\Configuration\Loader\Env\EnvVarParser;
use Smile\GdprDump\Util\Objects;
use stdClass;

final class VersionApplier
{
    public function __construct(private EnvVarParser $envVarParser)
    {
    }

    /**
     * Get the application version from the specified data object.
     *
     * @throws ParseException
     */
    public function detectVersion(stdClass $configuration): ?string
    {
        if (!property_exists($configuration, 'version')) {
            return null;
        }

        if (!is_string($configuration->version) || $configuration->version === '') {
            throw new ParseException('The parameter "version" must be a non-empty string.');
        }

        return $this->envVarParser->parse($configuration->version);
    }

    /**
     * Get file imports that are contained in versioned data.
     */
    public function getImports(stdClass $configuration, ?string $version): array
    {
        $imports = [];

        $this->applyCallableToVersionedData(
            $configuration,
            $version,
            function (stdClass $versionData) use (&$imports): void {
                if (isset($versionData->extends)) {
                    $imports = array_merge($imports, (array) $versionData->extends);
                }
            }
        );

        return $imports;
    }

    /**
     * Merge `if_version` blocks that match the specified version.
     *
     * Throws an exception if there are `if_version` blocks to merge and an empty version was provided.
     *
     * @throws ParseException
     */
    public function applyVersion(stdClass $configuration, ?string $version): void
    {
        $this->applyCallableToVersionedData(
            $configuration,
            $version,
            function (stdClass $versionData) use ($configuration): void {
                Objects::merge($configuration, $versionData);
            }
        );
    }

    /**
     * Apply the specified callback to the `if_version` fragments that match the specified version.
     */
    private function applyCallableToVersionedData(stdClass $configuration, ?string $version, callable $callback): void
    {
        $versionsData = $this->getVersionsData($configuration);
        if (!$versionsData) {
            // Nothing to do because no if_version section was found
            return;
        }

        if ($version === null || $version === '') {
            throw new ParseException('The application version must be specified in the configuration.');
        }

        // Merge version-specific data into the configuration
        $versionMatcher = new VersionMatcher();

        foreach ($versionsData as $requirement => $versionData) {
            if (!$versionData instanceof stdClass) {
                throw new ParseException(sprintf('Could not parse data for version requirement "%s".', $requirement));
            }

            if ($versionMatcher->match((string) $requirement, $version)) {
                $callback($versionData);
            }
        }
    }

    /**
     * Get the version-specific data from the specified object.
     */
    private function getVersionsData(stdClass $configuration): array
    {
        if (!property_exists($configuration, 'if_version')) {
            return [];
        }

        if (!$configuration->if_version instanceof stdClass) {
            throw new ParseException('The parameter "if_version" must be an object.');
        }

        return get_object_vars($configuration->if_version);
    }
}
