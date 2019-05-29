<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Tests\Config\Resolver;

use Smile\Anonymizer\Config\Resolver\PathResolver;
use Smile\Anonymizer\Tests\TestCase;

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
     * Check if the resolver can resolve paths that start with the "~/" shortcut.
     *
     * @doesNotPerformAssertions
     */
    public function testResolveHomePath()
    {
        if (!function_exists('posix_getuid')) {
            return;
        }

        $resolver = new PathResolver();
        $resolvedPath = $resolver->resolve('~/');
        $this->assertDirectoryExists($resolvedPath);
    }

    /**
     * Test if the condition is properly parsed.
     *
     * @expectedException \RuntimeException
     */
    public function testFileNotFound()
    {
        $resolver = new PathResolver();
        $resolver->resolve('notExists');
    }
}
