<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Resource;

use Smile\GdprDump\Configuration\Resource\FileResource;
use Smile\GdprDump\Configuration\Resource\ResourceStack;
use Smile\GdprDump\Tests\Unit\TestCase;

final class ResourceStackTest extends TestCase
{
    /**
     * Test the resource stack object.
     */
    public function testResourceStack(): void
    {
        $stack = new ResourceStack();
        $this->assertTrue($stack->isEmpty());

        $resource1 = $this->createResource();
        $resource2 = $this->createResource();

        $stack->push($resource1);
        $stack->push($resource2);
        $this->assertFalse($stack->isEmpty());

        $this->assertSame($resource2, $stack->pop());
        $this->assertSame($resource1, $stack->pop());
        $this->assertTrue($stack->isEmpty());
    }

    /**
     * Test the "clear" method.
     */
    public function testClearStack(): void
    {
        $stack = new ResourceStack();
        $stack->push($this->createResource());
        $stack->push($this->createResource());
        $stack->push($this->createResource());
        $this->assertFalse($stack->isEmpty());
        $stack->clear();
        $this->assertTrue($stack->isEmpty());
    }

    /**
     * Create a resource with a random value.
     */
    private function createResource(): FileResource
    {
        return new FileResource('file' . random_int(1, 1000000));
    }
}
