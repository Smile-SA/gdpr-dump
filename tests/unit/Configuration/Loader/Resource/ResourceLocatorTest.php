<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Configuration\Loader\Resource;

use Smile\GdprDump\Configuration\Exception\FileNotFoundException;
use Smile\GdprDump\Configuration\Loader\Resource\ResourceLocator;
use Smile\GdprDump\Tests\Unit\TestCase;

final class ResourceLocatorTest extends TestCase
{
    /**
     * Check if the locator can resolve relative paths.
     */
    public function testResolveRelativePath(): void
    {
        $path = 'tests/unit/Resources/config/test_locator/config.yaml';
        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);

        $locator = $this->createResourceLocator();
        $resolved = $locator->locate($path);
        $this->assertSame($this->getBasePath() . DIRECTORY_SEPARATOR . $path, $resolved);

        $locator = $this->createResourceLocator();
        $resolved = $locator->locate($path, $this->getBasePath());
        $this->assertSame($this->getBasePath() . DIRECTORY_SEPARATOR . $path, $resolved);
    }

    /**
     * Check if the locator can resolve absolute paths.
     */
    public function testResolveAbsolutePath(): void
    {
        $path = $this->getResource('config/test_locator/config.yaml');
        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);

        $locator = $this->createResourceLocator();
        $resolved = $locator->locate($path);
        $this->assertSame($path, $resolved);

        $locator = $this->createResourceLocator();
        $resolved = $locator->locate($path, $this->getBasePath());
        $this->assertSame($path, $resolved);
    }

    /**
     * Check if the locator can resolve template aliases.
     */
    public function testResolveTemplate(): void
    {
        $locator = $this->createResourceLocator();
        $resolved = $locator->locate('template');
        $this->assertStringContainsString('templates/template.yaml', $resolved);
    }

    /**
     * Assert that an exception is thrown when the templates directory does not exist.
     */
    public function testTemplatesDirectoryNotExists(): void
    {
        $locator = $this->createResourceLocator('not_exists');
        $this->expectException(FileNotFoundException::class);
        $locator->locate($this->getResource('config/test_locator/config.yaml'));
    }

    /**
     * Assert that an exception is thrown when a file with a relative path is not found.
     */
    public function testFileWithRelativePathNotFound(): void
    {
        $locator = $this->createResourceLocator();
        $this->expectException(FileNotFoundException::class);
        $locator->locate('not_exists');
    }

    /**
     * Assert that an exception is thrown when a file with an absolute path is not found.
     */
    public function testFileWithAbsolutePathNotFound(): void
    {
        $locator = $this->createResourceLocator();
        $this->expectException(FileNotFoundException::class);
        $locator->locate('/not/exists');
    }

    /**
     * Create a file locator object.
     */
    private function createResourceLocator(?string $templatesDirectory = null): ResourceLocator
    {
        $templatesDirectory = $this->getResource($templatesDirectory ?? 'config/test_locator/templates');

        return new ResourceLocator($templatesDirectory);
    }
}
