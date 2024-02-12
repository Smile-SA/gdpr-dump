<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Generator;

use Smile\GdprDump\Converter\Generator\NumberBetween;
use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;

class NumberBetweenTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = $this->createConverter(NumberBetween::class, ['min' => 0, 'max' => 100]);

        $value = $converter->convert(null);
        $this->assertGreaterThanOrEqual(0, $value);
        $this->assertLessThanOrEqual(100, $value);

        $value = $converter->convert('value');
        $this->assertGreaterThanOrEqual(0, $value);
        $this->assertLessThanOrEqual(100, $value);
    }

    /**
     * Assert that an exception is thrown when the min value is not set.
     */
    public function testMinNotSet(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(NumberBetween::class, ['max' => 0]);
    }

    /**
     * Assert that an exception is thrown when the max value is not set.
     */
    public function testMaxNotSet(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(NumberBetween::class, ['min' => 0]);
    }

    /**
     * Assert that an exception is thrown when the min value is greater than the max value.
     */
    public function testMinGreaterThanMax(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(NumberBetween::class, ['min' => 100, 'max' => 0]);
    }
}
