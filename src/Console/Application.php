<?php

declare(strict_types=1);

namespace Smile\GdprDump\Console;

use Composer\InstalledVersions;
use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    private const PACKAGE_NAME = 'smile/gdpr-dump';

    public function __construct()
    {
        parent::__construct('GdprDump', $this->getPackageVersion());
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
