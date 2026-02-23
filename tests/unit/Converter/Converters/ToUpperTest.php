<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Converters;

use Smile\GdprDump\Converter\Converters\ToUpper;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;

final class ToUpperTest extends TestCase
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
