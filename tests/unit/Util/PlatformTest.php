<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Util;

use Smile\GdprDump\Tests\Unit\TestCase;
use Smile\GdprDump\Util\Platform;

final class PlatformTest extends TestCase
{
    /**
     * Test the "isAbsolutePath" method.
     */
    public function testIsAbsolutePath(): void
    {
        // Empty string
        $this->assertFalse(Platform::isAbsolutePath(''));

        // Relative path
        $this->assertFalse(Platform::isAbsolutePath('file'));
        $this->assertFalse(Platform::isAbsolutePath('file.yaml'));
        $this->assertFalse(Platform::isAbsolutePath('path/to/file'));

        $this->assertFalse(Platform::isAbsolutePath('./'));
        $this->assertFalse(Platform::isAbsolutePath('./file'));
        $this->assertFalse(Platform::isAbsolutePath('../'));
        $this->assertFalse(Platform::isAbsolutePath('../file'));

        // Relative path (scheme)
        $this->assertFalse(Platform::isAbsolutePath('file://file'));
        $this->assertFalse(Platform::isAbsolutePath('file://path/to/file'));
        $this->assertFalse(Platform::isAbsolutePath('phar://file'));
        $this->assertFalse(Platform::isAbsolutePath('phar://path/to/file'));

        // Absolute path (Linux)
        $this->assertTrue(Platform::isAbsolutePath('/'));
        $this->assertTrue(Platform::isAbsolutePath('/path/to/file'));

        // Absolute path (Windows)
        $this->assertTrue(Platform::isAbsolutePath('C:\\'));
        $this->assertTrue(Platform::isAbsolutePath('C:\\file'));

        // Absolute path (network)
        $this->assertTrue(Platform::isAbsolutePath('//'));
        $this->assertTrue(Platform::isAbsolutePath('//file'));
    }
}
