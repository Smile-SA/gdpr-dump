<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Anonymizer;

use Smile\GdprDump\Converter\Anonymizer\AnonymizeDateTime;
use Smile\GdprDump\Converter\Parameters\ValidationException;
use UnexpectedValueException;

class AnonymizeDateTimeTest extends AnonymizeDateTest
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = new AnonymizeDateTime();

        $date = '1990-12-31 12:05:41';
        $value = $converter->convert($date);
        $this->assertDateIsAnonymized($value, $date, 'Y-m-d H:i:s');
    }

    /**
     * Test using a custom date format.
     */
    public function testFormatParameter(): void
    {
        $format = 'd/m/Y H:i:s';
        $converter = new AnonymizeDateTime(['format' => $format]);

        $date = '31/12/1990 12:05:41';
        $value = $converter->convert($date);
        $this->assertDateIsAnonymized($value, $date, $format);
    }

    /**
     * Assert that an exception is thrown when the parameter "format" is empty.
     */
    public function testEmptyFormat(): void
    {
        $this->expectException(ValidationException::class);
        new AnonymizeDateTime(['format' => '']);
    }

    /**
     * Assert that an exception is thrown when an invalid date is provided.
     */
    public function testInvalidDateFormat(): void
    {
        $converter = new AnonymizeDateTime();
        $this->expectException(UnexpectedValueException::class);
        $converter->convert('invalidFormat');
    }
}
