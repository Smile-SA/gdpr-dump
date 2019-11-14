<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Base;

use Smile\GdprDump\Converter\Base\NumberBetween;
use Smile\GdprDump\Tests\Unit\TestCase;

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
     *
     * @expectedException \InvalidArgumentException
     */
    public function testMinNotSet()
    {
        new NumberBetween(['max' => 0]);
    }

    /**
     * Assert that an exception is thrown when the max value is not set.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testMaxNotSet()
    {
        new NumberBetween(['min' => 0]);
    }

    /**
     * Assert that an exception is thrown when the min value is greater than the max value.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testMinGreaterThanMax()
    {
        new NumberBetween(['min' => 100, 'max' => 0]);
    }
}
