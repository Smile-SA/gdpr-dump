<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Converters\Internal;

use OverflowException;
use Smile\GdprDump\Converter\Converters\Internal\Unique;
use Smile\GdprDump\Converter\Converters\SetNull;
use Smile\GdprDump\Converter\Exception\InvalidParameterException;
use Smile\GdprDump\Tests\Framework\Mock\Converter\ConverterMock;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;
use stdClass;

final class UniqueTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = $this->createConverter(Unique::class, [
            'converter' => $this->createConverter(ConverterMock::class),
        ]);
        $value = $converter->convert('1');
        $this->assertSame('test_1', $value);
    }

    /**
     * Test if NULL values are ignored by the converter.
     */
    public function testNullValuesIgnored(): void
    {
        $converter = $this->createConverter(Unique::class, [
            'converter' => $this->createConverter(SetNull::class),
        ]);
        $value = $converter->convert('1');
        $this->assertNull($value);

        // Should not throw an exception, the unique converter ignores values converted to null
        $converter->convert('1');
        $converter->convert('1');
    }

    /**
     * Assert that an exception is thrown when the converter fails to generate a unique value.
     */
    public function testFailedUniqueValue(): void
    {
        $converter = $this->createConverter(Unique::class, [
            'converter' => $this->createConverter(ConverterMock::class),
        ]);
        $converter->convert('1');
        $this->expectException(OverflowException::class);
        $converter->convert('1');
    }

    /**
     * Assert that an exception is thrown when the converter is not set.
     */
    public function testConverterNotSet(): void
    {
        $this->expectException(InvalidParameterException::class);
        $this->createConverter(Unique::class);
    }

    /**
     * Assert that an exception is thrown when the parameter "converter" is not an instance of Converter.
     */
    public function testConverterNotValid(): void
    {
        $this->expectException(InvalidParameterException::class);
        $this->createConverter(Unique::class, ['converter' => new stdClass()]);
    }
}
