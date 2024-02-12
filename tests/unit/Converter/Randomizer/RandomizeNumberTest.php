<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Randomizer;

use Smile\GdprDump\Converter\Randomizer\RandomizeNumber;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;

class RandomizeNumberTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = $this->createConverter(RandomizeNumber::class);

        $value = $converter->convert(null);
        $this->assertSame('', $value);

        $value = $converter->convert('+33601010101');
        $this->assertStringStartsWith('+', $value);
        $this->assertSame(12, strlen($value));

        // Assert that the part without the "+" is still a numeric value
        $valueWithoutPlus = substr($value, 1);
        $this->assertTrue(is_numeric($valueWithoutPlus));
        $this->assertNotSame('33601010101', $valueWithoutPlus);
    }
}
