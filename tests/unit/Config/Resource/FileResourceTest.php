<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Resource;

use Smile\GdprDump\Configuration\Resource\FileResource;
use Smile\GdprDump\Tests\Unit\TestCase;

final class FileResourceTest extends TestCase
{
    /**
     * Test the file resource object.
     */
    public function testFileResource(): void
    {
        $path = 'path/to/file.yaml';
        $resource = new FileResource($path);
        $this->assertSame($path, $resource->getInput());
        $this->assertSame('yaml', $resource->getExtension());

        $path = '';
        $resource = new FileResource($path);
        $this->assertSame($path, $resource->getInput());
        $this->assertSame('', $resource->getExtension());
    }
}
