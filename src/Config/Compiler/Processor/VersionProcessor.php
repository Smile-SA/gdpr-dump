<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Compiler\Processor;

use Smile\GdprDump\Config\Compiler\CompileException;
use Smile\GdprDump\Config\Compiler\Processor\Version\MissingVersionException;
use Smile\GdprDump\Config\Compiler\Processor\Version\VersionMatcher;
use Smile\GdprDump\Config\ConfigInterface;

final class VersionProcessor implements ProcessorInterface
{
    /**
     * Process the "if_version" parameter.
     *
     * @throws CompileException
     */
    public function process(ConfigInterface $config): void
    {
        $requiresVersion = (bool) $config->get('requires_version');
        $version = (string) $config->get('version');
        $versionsData = (array) $config->get('if_version');

        if ($version === '') {
            // Check if version is mandatory
            if ($requiresVersion) {
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
