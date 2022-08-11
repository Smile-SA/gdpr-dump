<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Loader;

use Smile\GdprDump\Config\Loader\FileLocator;
use Smile\GdprDump\Config\Loader\FileNotFoundException;
use Smile\GdprDump\Tests\Unit\TestCase;

class FileLocatorTest extends TestCase
{
    /**
     * Check if the locator can resolve relative paths.
     */
    public function testResolveRelativePath(): void
    {
        $path = 'tests/unit/Resources/config/templates/test.yaml';
        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);

        $locator = $this->createFileLocator();
        $resolved = $locator->locate($path);
        $this->assertSame($this->getBasePath() . DIRECTORY_SEPARATOR . $path, $resolved);

        $locator = $this->createFileLocator();
        $resolved = $locator->locate($path, $this->getBasePath());
        $this->assertSame($this->getBasePath() . DIRECTORY_SEPARATOR . $path, $resolved);
    }

    /**
     * Check if the locator can resolve absolute paths.
     */
    public function testResolveAbsolutePath(): void
    {
        $path = $this->getResource('config/templates/test.yaml');
        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);

        $locator = $this->createFileLocator();
        $resolved = $locator->locate($path);
        $this->assertSame($path, $resolved);

        $locator = $this->createFileLocator();
        $resolved = $locator->locate($path, $this->getBasePath());
        $this->assertSame($path, $resolved);
    }

    /**
     * Check if the locator can resolve template aliases.
     */
    public function testResolveTemplate(): void
    {
        $locator = $this->createFileLocator();

        $resolved = $locator->locate('test');
        $this->assertStringContainsString('test.yaml', $resolved);
    }

    /**
     * Assert that an exception is thrown when a file with a relative path is not found.
     */
    public function testFileWithRelativePathNotFound(): void
    {
        $locator = $this->createFileLocator();
        $this->expectException(FileNotFoundException::class);
        $locator->locate('not_exists');
    }

    /**
     * Assert that an exception is thrown when a file with an absolute path is not found.
     */
    public function testFileWithAbsolutePathNotFound(): void
    {
        $locator = $this->createFileLocator();
        $this->expectException(FileNotFoundException::class);
        $locator->locate('/not/exists');
    }

    /**
     * Create a file locator object.
     */
    private function createFileLocator(): FileLocator
    {
        $templatesDirectory = $this->getResource('config/templates');

        return new FileLocator($templatesDirectory);
    }
}
