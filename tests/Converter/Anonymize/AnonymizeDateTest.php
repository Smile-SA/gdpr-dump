<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Tests\Converter\Anonymize;

use PHPUnit\Framework\TestCase;
use Smile\Anonymizer\Converter\Anonymizer\AnonymizeDate;

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
        $this->assertDateIsAnonymized($date, $anonymizedDate, 'Y-m-d');
    }

    /**
     * Test the converter with a custom date format.
     */
    public function testCustomFormat()
    {
        $format = 'd/m/Y';
        $converter = new AnonymizeDate(['format' => $format]);

        $date = '31/12/1990';
        $anonymizedDate = $converter->convert($date);
        $this->assertDateIsAnonymized($date, $anonymizedDate, $format);
    }

    /**
     * Test if an exception is thrown when an invalid date is provided.
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
     * @param string $expected
     * @param string $actual
     * @param string $format
     */
    protected function assertDateIsAnonymized($expected, $actual, $format)
    {
        $expectedDate = \DateTime::createFromFormat($format, $expected);
        $actualDate = \DateTime::createFromFormat($format, $actual);

        // The year must not have changed
        $this->assertSame($expectedDate->format('Y'), $actualDate->format('Y'));

        // The day and month must have been randomized
        $this->assertTrue($expectedDate->format('n') !== $actualDate->format('n') || $expectedDate->format('j') !== $actualDate->format('j'));

        // The time must not have changed
        $this->assertSame($expectedDate->format('H:i:s'), $actualDate->format('H:i:s'));
    }
}
