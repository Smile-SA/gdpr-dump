<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Resolver;

use Smile\GdprDump\Config\Resolver\PathResolver;
use Smile\GdprDump\Tests\Unit\TestCase;

class PathResolverTest extends TestCase
{
    /**
     * Check if the resolver can resolve relative paths.
     */
    public function testResolveRelativePath()
    {
        $relativePath = 'tests/unit/Resources/config/templates/test.yaml';

        $resolver = $this->createPathResolver();
        $resolvedPath = $resolver->resolve($relativePath);
        $this->assertSame($this->getBasePath() . '/' . $relativePath, $resolvedPath);
    }

    /**
     * Check if the resolver can resolve absolute paths.
     */
    public function testResolveAbsolutePath()
    {
        $absolutePath = $this->getResource('config/templates/test.yaml');

        $resolver = $this->createPathResolver();
        $resolvedPath = $resolver->resolve($absolutePath);
        $this->assertSame($absolutePath, $resolvedPath);
    }

    /**
     * Check if the resolver can resolve template aliases.
     */
    public function testResolveTemplate()
    {
        $resolver = $this->createPathResolver();

        // 'test' should be resolved into the "test.yaml" template
        $resolvedPath = $resolver->resolve('test');
        $this->assertContains('test.yaml', $resolvedPath);
    }

    /**
     * Assert that an exception is thrown when a file with a relative path is not found.
     *
     * @expectedException \Smile\GdprDump\Config\Resolver\FileNotFoundException
     */
    public function testFileWithRelativePathNotFound()
    {
        $resolver = $this->createPathResolver();
        $resolver->resolve('not_exists');
    }

    /**
     * Assert that an exception is thrown when a file with an absolute path is not found.
     *
     * @expectedException \Smile\GdprDump\Config\Resolver\FileNotFoundException
     */
    public function testFileWithAbsolutePathNotFound()
    {
        $resolver = $this->createPathResolver();
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
