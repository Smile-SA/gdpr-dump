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
        $path = 'config/templates/magento2.yaml';

        $resolver = new PathResolver();
        $resolvedPath = $resolver->resolve($path);
        $this->assertContains(DIRECTORY_SEPARATOR . $path, $resolvedPath);
    }

    /**
     * Check if the resolver can resolve absolute paths.
     */
    public function testResolveAbsolutePath()
    {
        $path = APP_ROOT . '/config/templates/magento2.yaml';

        $resolver = new PathResolver();
        $resolvedPath = $resolver->resolve($path);
        $this->assertSame($path, $resolvedPath);
    }

    /**
     * Check if the resolver can resolve template aliases.
     */
    public function testResolveTemplate()
    {
        $resolver = new PathResolver();
        $resolvedPath = $resolver->resolve('magento2');
        $this->assertContains('magento2.yaml', $resolvedPath);
    }

    /**
     * Test if an exception is thrown when a file with a relative path is not found.
     *
     * @expectedException \Smile\GdprDump\Config\Resolver\FileNotFoundException
     */
    public function testFileWithRelativePathNotFound()
    {
        $resolver = new PathResolver();
        $resolver->resolve('notExists');
    }

    /**
     * Test if an exception is thrown when a file with an absolute path is not found.
     *
     * @expectedException \Smile\GdprDump\Config\Resolver\FileNotFoundException
     */
    public function testFileWithAbsolutePathNotFound()
    {
        $resolver = new PathResolver();
        $resolver->resolve('/not/exists');
    }
}
