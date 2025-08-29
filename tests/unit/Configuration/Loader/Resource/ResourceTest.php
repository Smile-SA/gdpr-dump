<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Configuration\Loader\Resource;

use Smile\GdprDump\Configuration\Loader\Resource\Resource;
use Smile\GdprDump\Tests\Unit\TestCase;

final class ResourceTest extends TestCase
{
    /**
     * Test the resource object.
     */
    public function testResource(): void
    {
        $path = 'path/to/file.yaml';
        $resource = new Resource($path);
        $this->assertSame($path, $resource->getInput());
        $this->assertTrue($resource->isFile());

        $path = '{}';
        $resource = new Resource($path, false);
        $this->assertSame($path, $resource->getInput());
        $this->assertFalse($resource->isFile());
    }
}
