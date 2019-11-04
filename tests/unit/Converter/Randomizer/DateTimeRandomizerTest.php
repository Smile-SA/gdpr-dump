<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Randomizer;

use DateTime;
use Smile\GdprDump\Converter\Randomizer\RandomizeDateTime;

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
        $converter = new RandomizeDateTime(['min_year' => 1970, 'max_year' => 2020]);

        $date = '1990-12-31 12:05:41';
        $randomizedDate = $converter->convert($date);
        $this->assertDateIsRandomized($randomizedDate, $date, 'Y-m-d H:i:s');
    }

    /**
     * Test if the current year is used if the min/max years are set to null.
     */
    public function testNullYears()
    {
        $converter = new RandomizeDateTime(['min_year' => null, 'max_year' => null]);

        $date = '1990-12-31 12:05:41';
        $randomizedDate = $converter->convert($date);

        $currentYear = (new DateTime())->format('Y');
        $randomizedYear = (new DateTime($randomizedDate))->format('Y');
        $this->assertSame($currentYear, $randomizedYear);
    }

    /**
     * Assert that an exception is thrown when the min year is higher than the max year.
     *
     * @expectedException \Exception
     */
    public function testYearConflict()
    {
        $converter = new RandomizeDateTime(['min_year' => 2020, 'max_year' => 2019]);
        $converter->convert('1990-12-31');
    }
}
