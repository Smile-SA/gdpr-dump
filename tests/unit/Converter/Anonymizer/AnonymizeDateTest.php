<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Anonymizer;

use DateTime;
use Smile\GdprDump\Converter\Anonymizer\AnonymizeDate;
use Smile\GdprDump\Tests\Unit\TestCase;

class AnonymizeDateTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter()
    {
        $converter = new AnonymizeDate();

        $date = '1990-12-31';
        $anonymizedDate = $converter->convert($date);
        $this->assertDateIsAnonymized($anonymizedDate, $date, 'Y-m-d');
    }

    /**
     * Test the converter with a custom date format.
     */
    public function testFormatParameter()
    {
        $format = 'd/m/Y';
        $converter = new AnonymizeDate(['format' => $format]);

        $date = '31/12/1990';
        $anonymizedDate = $converter->convert($date);
        $this->assertDateIsAnonymized($anonymizedDate, $date, $format);
    }

    /**
     * Assert that an exception is thrown when the parameter "format" is empty.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testEmptyFormat()
    {
        new AnonymizeDate(['format' => '']);
    }

    /**
     * Assert that an exception is thrown when an invalid date is provided.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testInvalidDateFormat()
    {
        $converter = new AnonymizeDate();
        $converter->convert('invalidFormat');
    }

    /**
     * Assert that a date is anonymized.
     *
     * @param string $anonymized
     * @param string $actual
     * @param string $format
     */
    protected function assertDateIsAnonymized(string $anonymized, string $actual, string $format)
    {
        $anonymizedDate = DateTime::createFromFormat($format, $anonymized);
        $actualDate = DateTime::createFromFormat($format, $actual);

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
