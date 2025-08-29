<?php

declare(strict_types=1);

namespace Smile\GdprDump\Console;

use Composer\InstalledVersions;
use Symfony\Component\Console\Application as BaseApplication;

final class Application extends BaseApplication
{
    private const PACKAGE_NAME = 'smile/gdpr-dump';

    public function __construct()
    {
        parent::__construct('GdprDump', $this->getPackageVersion());

        // Remove the --quiet and --silent options
        $options = $this->getDefinition()->getOptions();
        unset($options['quiet']);
        unset($options['silent']); // introduced in symfony 7.2
        $this->getDefinition()->setOptions($options);
    }

    /**
     * Get the application version.
     */
    private function getPackageVersion(): string
    {
        $prettyVersion = (string) InstalledVersions::getPrettyVersion(self::PACKAGE_NAME);
        $reference = (string) InstalledVersions::getReference(self::PACKAGE_NAME);

        if ($prettyVersion === '' || $reference === '') {
            return 'Unknown version';
        }

        if (preg_match('/[^v\d.]/', $prettyVersion) === 0) {
            // Tags
            return $prettyVersion;
        }

        // Branches (with ref)
        return $prettyVersion . '@' . substr($reference, 0, 7);
    }
}
