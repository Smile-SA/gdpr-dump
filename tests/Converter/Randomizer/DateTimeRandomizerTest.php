<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Tests\Converter\Randomizer;

use Smile\Anonymizer\Converter\Randomizer\RandomizeDateTime;

class DateTimeRandomizerTest extends DateRandomizerTest
{
    /**
     * Test the converter.
     */
    public function testConverter()
    {
        $converter = new RandomizeDateTime();

        $date = '1990-12-31 12:05:41';
        $randomizedDate = $converter->convert($date);
        $this->assertDateIsRandomized($randomizedDate, $date, 'Y-m-d H:i:s');
    }

    /**
     * Test using a custom date format.
     */
    public function testFormatParameter()
    {
        $format = 'd/m/Y H:i:s';
        $converter = new RandomizeDateTime(['format' => $format]);

        $date = '31/12/1990 12:05:41';
        $randomizedDate = $converter->convert($date);
        $this->assertDateIsRandomized($randomizedDate, $date, $format);
    }

    /**
     * Test the converter with a custom min/max year.
     */
    public function testYearParameters()
    {
        $converter = new RandomizeDateTime(['minYear' => 1970, 'maxYear' => 2020]);

        $date = '1990-12-31 12:05:41';
        $randomizedDate = $converter->convert($date);
        $this->assertDateIsRandomized($randomizedDate, $date, 'Y-m-d H:i:s');
    }

    /**
     * Test if an exception is thrown when the min year is higher than the max year.
     *
     * @expectedException \Exception
     */
    public function testYearConflict()
    {
        $converter = new RandomizeDateTime(['minYear' => 2020, 'maxYear' => 2019]);
        $converter->convert('1990-12-31');
    }
}
