<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Converters;

use Smile\GdprDump\Converter\Converters\AnonymizeDateTime;
use Smile\GdprDump\Converter\Exception\InvalidParameterException;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;
use UnexpectedValueException;

final class AnonymizeDateTimeTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = $this->createConverter(AnonymizeDateTime::class);

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
        $converter = $this->createConverter(AnonymizeDateTime::class, ['format' => $format]);

        $date = '31/12/1990 12:05:41';
        $value = $converter->convert($date);
        $this->assertDateIsAnonymized($value, $date, $format);
    }

    /**
     * Assert that an exception is thrown when the parameter "format" is empty.
     */
    public function testEmptyFormat(): void
    {
        $this->expectException(InvalidParameterException::class);
        $this->createConverter(AnonymizeDateTime::class, ['format' => '']);
    }

    /**
     * Assert that an exception is thrown when an invalid date is provided.
     */
    public function testInvalidDateFormat(): void
    {
        $converter = $this->createConverter(AnonymizeDateTime::class);
        $this->expectException(UnexpectedValueException::class);
        $converter->convert('invalidFormat');
    }
}
