<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Tests\Converter\Randomizer;

use PHPUnit\Framework\TestCase;
use Smile\Anonymizer\Converter\Randomizer\RandomizeDate;

class DateRandomizerTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter()
    {
        $converter = new RandomizeDate();

        $date = '1990-12-31';
        $randomizedDate = $converter->convert($date);
        $this->assertDateIsRandomized($randomizedDate, $date, 'Y-m-d');
    }

    /**
     * Test the converter with a custom date format.
     */
    public function testFormatParameter()
    {
        $format = 'd/m/Y';
        $converter = new RandomizeDate(['format' => $format]);

        $date = '31/12/1990';
        $randomizedDate = $converter->convert($date);
        $this->assertDateIsRandomized($randomizedDate, $date, $format);
    }

    /**
     * Test the converter with a custom min year.
     */
    public function testYearParameters()
    {
        $converter = new RandomizeDate(['minYear' => 1970, 'maxYear' => 2020]);

        $date = '1990-12-31';
        $randomizedDate = $converter->convert($date);
        $this->assertDateIsRandomized($randomizedDate, $date, 'Y-m-d');
    }

    /**
     * Test if an exception is thrown when the min year is higher than the max year.
     *
     * @expectedException \Exception
     */
    public function testYearConflict()
    {
        $converter = new RandomizeDate(['minYear' => 2020, 'maxYear' => 2019]);
        $converter->convert('1990-12-31');
    }

    /**
     * Assert that a date is randomized.
     *
     * @param string $randomized
     * @param string $actual
     * @param string $format
     */
    protected function assertDateIsRandomized($randomized, $actual, $format)
    {
        $randomizedDate = \DateTime::createFromFormat($format, $randomized);
        $actualDate = \DateTime::createFromFormat($format, $actual);

        $this->assertTrue($randomizedDate != $actualDate);
    }
}
