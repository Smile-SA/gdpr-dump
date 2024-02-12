<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Generator;

use DateTime;
use Smile\GdprDump\Converter\Generator\RandomDate;
use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;

class RandomDateTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = $this->createConverter(RandomDate::class);

        $value = $converter->convert(null);
        $this->assertNotNull($value);

        $date = '1990-12-31';
        $value = $converter->convert($date);
        $this->assertDateIsRandomized($value, $date, 'Y-m-d');
    }

    /**
     * Test the converter with a custom date format.
     */
    public function testFormatParameter(): void
    {
        $format = 'd/m/Y';
        $converter = $this->createConverter(RandomDate::class, ['format' => $format]);

        $date = '31/12/1990';
        $value = $converter->convert($date);
        $this->assertDateIsRandomized($value, $date, $format);
    }

    /**
     * Test the converter with a custom min year.
     */
    public function testYearParameters(): void
    {
        $converter = $this->createConverter(RandomDate::class, [
            'min_year' => 1970,
            'max_year' => 2020,
        ]);

        $date = '1990-12-31';
        $value = $converter->convert($date);
        $this->assertDateIsRandomized($value, $date, 'Y-m-d');
    }

    /**
     * Test if the current year is used if the min/max years are set to null.
     */
    public function testNullYears(): void
    {
        $converter = $this->createConverter(RandomDate::class, [
            'min_year' => null,
            'max_year' => null,
        ]);

        $date = '1990-12-31';
        $value = $converter->convert($date);

        $currentYear = (new DateTime())->format('Y');
        $randomizedYear = (new DateTime($value))->format('Y');
        $this->assertSame($currentYear, $randomizedYear);
    }

    /**
     * Assert that an exception is thrown when the parameter "format" is empty.
     */
    public function testEmptyFormat(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(RandomDate::class, ['format' => '']);
    }

    /**
     * Assert that an exception is thrown when the min year is higher than the max year.
     */
    public function testYearConflict(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(RandomDate::class, [
            'min_year' => 2020,
            'max_year' => 2019,
        ]);
    }

    /**
     * Assert that a date is randomized.
     */
    protected function assertDateIsRandomized(string $randomized, string $actual, string $format): void
    {
        $randomizedDate = DateTime::createFromFormat($format, $randomized);
        $actualDate = DateTime::createFromFormat($format, $actual);

        $this->assertTrue($randomizedDate !== $actualDate);
    }
}
