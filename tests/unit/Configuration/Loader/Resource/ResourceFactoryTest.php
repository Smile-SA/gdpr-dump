<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Configuration\Loader\Resource;

use Smile\GdprDump\Configuration\Loader\Resource\ResourceFactory;
use Smile\GdprDump\Configuration\Loader\Resource\ResourceLocator;
use Smile\GdprDump\Tests\Unit\TestCase;

final class ResourceFactoryTest extends TestCase
{
    /**
     * Test the "createFileResource" method.
     */
    public function testCreateFileResource(): void
    {
        $factory = $this->createFactory();

        $resource = $factory->createFileResource('template');
        $this->assertStringContainsString('templates/template.yaml', $resource->getInput());
        $this->assertTrue($resource->isFile());
    }

    /**
     * Test the "createStringResource" method.
     */
    public function testCreateStringResource(): void
    {
        $factory = $this->createFactory();

        $path = '{}';
        $resource = $factory->createStringResource($path);
        $this->assertSame($path, $resource->getInput());
        $this->assertFalse($resource->isFile());
    }

    /**
     * Create a resource factory.
     */
    private function createFactory(): ResourceFactory
    {
        $resourceLocator = new ResourceLocator($this->getResource('config/test_loader/templates'));

        return new ResourceFactory($resourceLocator);
    }
}
