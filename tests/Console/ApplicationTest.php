<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Console;

use Smile\GdprDump\Console\Application;
use Smile\GdprDump\Tests\TestCase;

class ApplicationTest extends TestCase
{
    /**
     * Test the console application.
     */
    public function testConsoleApplication()
    {
        $application = new Application();

        $this->assertSame(APP_ROOT . '/config', $application->getConfigPath());
        $this->assertSame(APP_ROOT . '/vendor', $application->getVendorPath());
    }
}
