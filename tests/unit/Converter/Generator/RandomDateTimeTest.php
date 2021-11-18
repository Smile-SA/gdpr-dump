<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Generator;

use DateTime;
use Smile\GdprDump\Converter\Generator\RandomDateTime;
use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Converter\Randomizer\RandomizeDateTime;

class RandomDateTimeTest extends RandomDateTest
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = new RandomDateTime();

        $value = $converter->convert(null);
        $this->assertNotNull($value);

        $date = '1990-12-31 12:05:41';
        $value = $converter->convert($date);
        $this->assertDateIsRandomized($value, $date, 'Y-m-d H:i:s');
    }

    /**
     * Test using a custom date format.
     */
    public function testFormatParameter(): void
    {
        $format = 'd/m/Y H:i:s';
        $converter = new RandomDateTime(['format' => $format]);

        $date = '31/12/1990 12:05:41';
        $randomizedDate = $converter->convert($date);
        $this->assertDateIsRandomized($randomizedDate, $date, $format);
    }

    /**
     * Test the converter with a custom min/max year.
     */
    public function testYearParameters(): void
    {
        $converter = new RandomDateTime(['min_year' => 1970, 'max_year' => 2020]);

        $date = '1990-12-31 12:05:41';
        $randomizedDate = $converter->convert($date);
        $this->assertDateIsRandomized($randomizedDate, $date, 'Y-m-d H:i:s');
    }

    /**
     * Test if the current year is used if the min/max years are set to null.
     */
    public function testNullYears(): void
    {
        $converter = new RandomDateTime(['min_year' => null, 'max_year' => null]);

        $date = '1990-12-31 12:05:41';
        $randomizedDate = $converter->convert($date);

        $currentYear = (new DateTime())->format('Y');
        $randomizedYear = (new DateTime($randomizedDate))->format('Y');
        $this->assertSame($currentYear, $randomizedYear);
    }

    /**
     * Assert that an exception is thrown when the parameter "format" is empty.
     */
    public function testEmptyFormat(): void
    {
        $this->expectException(ValidationException::class);
        new RandomDateTime(['format' => '']);
    }

    /**
     * Assert that an exception is thrown when the min year is higher than the max year.
     */
    public function testYearConflict(): void
    {
        $this->expectException(ValidationException::class);
        new RandomDateTime(['min_year' => 2020, 'max_year' => 2019]);
    }
}
