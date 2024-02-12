<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Transformer;

use Smile\GdprDump\Converter\Transformer\ToUpper;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;

class ToUpperTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = $this->createConverter(ToUpper::class);

        $value = $converter->convert(null);
        $this->assertSame('', $value);

        $value = $converter->convert('VaLuE');
        $this->assertSame('VALUE', $value);
    }
}
