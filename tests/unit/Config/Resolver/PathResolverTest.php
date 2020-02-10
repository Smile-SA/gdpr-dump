<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Resolver;

use Smile\GdprDump\Config\Resolver\FileNotFoundException;
use Smile\GdprDump\Config\Resolver\PathResolver;
use Smile\GdprDump\Tests\Unit\TestCase;

class PathResolverTest extends TestCase
{
    /**
     * Check if the resolver can resolve relative paths.
     */
    public function testResolveRelativePath(): void
    {
        $relativePath = 'tests/unit/Resources/config/templates/test.yaml';
        $relativePath = str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

        $resolver = $this->createPathResolver();
        $resolvedPath = $resolver->resolve($relativePath);
        $this->assertSame($this->getBasePath() . DIRECTORY_SEPARATOR . $relativePath, $resolvedPath);
    }

    /**
     * Check if the resolver can resolve absolute paths.
     */
    public function testResolveAbsolutePath(): void
    {
        $absolutePath = $this->getResource('config/templates/test.yaml');
        $absolutePath = str_replace('/', DIRECTORY_SEPARATOR, $absolutePath);

        $resolver = $this->createPathResolver();
        $resolvedPath = $resolver->resolve($absolutePath);
        $this->assertSame($absolutePath, $resolvedPath);
    }

    /**
     * Check if the resolver can resolve template aliases.
     */
    public function testResolveTemplate(): void
    {
        $resolver = $this->createPathResolver();

        // 'test' should be resolved into the "test.yaml" template
        $resolvedPath = $resolver->resolve('test');
        $this->assertStringContainsString('test.yaml', $resolvedPath);
    }

    /**
     * Assert that an exception is thrown when a file with a relative path is not found.
     */
    public function testFileWithRelativePathNotFound(): void
    {
        $resolver = $this->createPathResolver();
        $this->expectException(FileNotFoundException::class);
        $resolver->resolve('not_exists');
    }

    /**
     * Assert that an exception is thrown when a file with an absolute path is not found.
     */
    public function testFileWithAbsolutePathNotFound(): void
    {
        $resolver = $this->createPathResolver();
        $this->expectException(FileNotFoundException::class);
        $resolver->resolve('/not/exists');
    }

    /**
     * Create a path resolver object.
     *
     * @return PathResolver
     */
    private function createPathResolver(): PathResolver
    {
        $templatesDirectory = $this->getResource('config/templates');

        return new PathResolver($templatesDirectory);
    }
}
