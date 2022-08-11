<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Anonymizer;

use DateTime;
use Smile\GdprDump\Converter\Anonymizer\AnonymizeDate;
use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Tests\Unit\TestCase;
use UnexpectedValueException;

class AnonymizeDateTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = new AnonymizeDate();

        $value = $converter->convert(null);
        $this->assertSame('', $value);

        $date = '1990-12-31';
        $value = $converter->convert($date);
        $this->assertDateIsAnonymized($value, $date, 'Y-m-d');
    }

    /**
     * Test the converter with a custom date format.
     */
    public function testFormatParameter(): void
    {
        $format = 'd/m/Y';
        $converter = new AnonymizeDate(['format' => $format]);

        $date = '31/12/1990';
        $value = $converter->convert($date);
        $this->assertDateIsAnonymized($value, $date, $format);
    }

    /**
     * Assert that an exception is thrown when the parameter "format" is empty.
     */
    public function testEmptyFormat(): void
    {
        $this->expectException(ValidationException::class);
        new AnonymizeDate(['format' => '']);
    }

    /**
     * Assert that an exception is thrown when an invalid date is provided.
     */
    public function testInvalidDateFormat(): void
    {
        $converter = new AnonymizeDate();
        $this->expectException(UnexpectedValueException::class);
        $converter->convert('invalidFormat');
    }

    /**
     * Assert that a date is anonymized.
     */
    protected function assertDateIsAnonymized(string $anonymized, string $actual, string $format): void
    {
        $anonymizedDate = DateTime::createFromFormat($format, $anonymized);
        $actualDate = DateTime::createFromFormat($format, $actual);

        // Make sure that PHP didn't fail to create the dates
        $this->assertNotFalse($anonymizedDate);
        $this->assertNotFalse($actualDate);

        // The year must not have changed
        $this->assertSame($anonymizedDate->format('Y'), $actualDate->format('Y'));

        // The day and month must have been randomized
        $this->assertTrue(
            $anonymizedDate->format('n') !== $actualDate->format('n')
            || $anonymizedDate->format('j') !== $actualDate->format('j')
        );

        // The time must not have changed
        $this->assertSame($anonymizedDate->format('H:i:s'), $actualDate->format('H:i:s'));
    }
}
