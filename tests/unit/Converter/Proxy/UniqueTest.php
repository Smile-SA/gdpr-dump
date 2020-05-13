<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Proxy;

use InvalidArgumentException;
use OverflowException;
use Smile\GdprDump\Converter\Proxy\Unique;
use Smile\GdprDump\Converter\Base\SetNull;
use Smile\GdprDump\Tests\Framework\Mock\Converter\ConverterMock;
use Smile\GdprDump\Tests\Unit\TestCase;

class UniqueTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter()
    {
        $parameters = [
            'converter' => new ConverterMock(),
        ];

        $converter = new Unique($parameters);
        $value = $converter->convert('1');
        $this->assertSame('test_1', $value);
    }

    /**
     * Test if NULL values are ignored by the converter.
     */
    public function testNullValuesIgnored()
    {
        $parameters = [
            'converter' => new SetNull(),
        ];

        $converter = new Unique($parameters);

        $value = $converter->convert('1');
        $this->assertNull($value);

        // Should not throw an exception, the unique converter ignores values converted to null
        $converter->convert('1');
        $converter->convert('1');
    }

    /**
     * Assert that an exception is thrown when the converter fails to generate a unique value.
     */
    public function testFailedUniqueValue()
    {
        $parameters = [
            'converter' => new ConverterMock(),
        ];

        $converter = new Unique($parameters);
        $converter->convert('1');
        $this->expectException(OverflowException::class);
        $converter->convert('1');
    }

    /**
     * Assert that an exception is thrown when the converter is not set.
     */
    public function testConverterNotSet()
    {
        $this->expectException(InvalidArgumentException::class);
        new Unique([]);
    }
}
