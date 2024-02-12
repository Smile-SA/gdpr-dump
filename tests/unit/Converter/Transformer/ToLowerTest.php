<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Transformer;

use Smile\GdprDump\Converter\Transformer\ToLower;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;

class ToLowerTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = $this->createConverter(ToLower::class);

        $value = $converter->convert(null);
        $this->assertSame('', $value);

        $value = $converter->convert('VaLuE');
        $this->assertSame('value', $value);
    }
}
