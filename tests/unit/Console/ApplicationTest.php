<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Console;

use Smile\GdprDump\Console\Application;
use Smile\GdprDump\Tests\Unit\TestCase;

final class ApplicationTest extends TestCase
{
    /**
     * Test the "getVersion" method.
     */
    public function testVersion(): void
    {
        $application = new Application();
        $installed = require __DIR__ . '/../../../vendor/composer/installed.php';
        $version = (string) $installed['versions']['smile/gdpr-dump']['pretty_version'];
        $reference = (string) $installed['versions']['smile/gdpr-dump']['reference'];

        // In the context of a unit test, the reference is always defined
        $expectedVersion = $version . '@' . substr($reference, 0, 7);
        $this->assertSame($application->getVersion(), $expectedVersion);
    }
}
