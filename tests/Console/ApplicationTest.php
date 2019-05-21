<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Tests\Console;

use PHPUnit\Framework\TestCase;
use Smile\Anonymizer\Console\Application;

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
