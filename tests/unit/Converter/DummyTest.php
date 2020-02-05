<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter;

use Smile\GdprDump\Converter\Dummy;
use Smile\GdprDump\Tests\Unit\TestCase;

class DummyTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter()
    {
        $converter = new Dummy();
        $value = $converter->convert('notAnonymized');
        $this->assertSame('notAnonymized', $value);
    }
}
