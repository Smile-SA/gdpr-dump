<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Resource;

use Smile\GdprDump\Configuration\Resource\FileResource;
use Smile\GdprDump\Configuration\Resource\JsonResource;
use Smile\GdprDump\Tests\Unit\TestCase;

final class JsonResourceTest extends TestCase
{
    /**
     * Test the json resource object.
     */
    public function testJsonResource(): void
    {
        $json = '{}';
        $resource = new JsonResource($json);
        $this->assertSame($json, $resource->getInput());
    }
}
