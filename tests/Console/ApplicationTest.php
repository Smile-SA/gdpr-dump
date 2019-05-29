<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Tests\Console;

use Smile\Anonymizer\Console\Application;
use Smile\Anonymizer\Tests\TestCase;

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
