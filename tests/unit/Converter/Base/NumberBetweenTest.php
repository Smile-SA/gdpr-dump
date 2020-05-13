<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Base;

use InvalidArgumentException;
use Smile\GdprDump\Converter\Base\NumberBetween;
use Smile\GdprDump\Tests\Unit\TestCase;
use UnexpectedValueException;

class NumberBetweenTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter()
    {
        $converter = new NumberBetween(['min' => 0, 'max' => 100]);

        $value = $converter->convert('value');
        $this->assertGreaterThanOrEqual(0, $value);
        $this->assertLessThanOrEqual(100, $value);
    }

    /**
     * Assert that an exception is thrown when the min value is not set.
     */
    public function testMinNotSet()
    {
        $this->expectException(InvalidArgumentException::class);
        new NumberBetween(['max' => 0]);
    }

    /**
     * Assert that an exception is thrown when the max value is not set.
     */
    public function testMaxNotSet()
    {
        $this->expectException(InvalidArgumentException::class);
        new NumberBetween(['min' => 0]);
    }

    /**
     * Assert that an exception is thrown when the min value is greater than the max value.
     */
    public function testMinGreaterThanMax()
    {
        $this->expectException(UnexpectedValueException::class);
        new NumberBetween(['min' => 100, 'max' => 0]);
    }
}
