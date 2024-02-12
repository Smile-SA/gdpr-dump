<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Proxy;

use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Converter\Proxy\Chain;
use Smile\GdprDump\Tests\Framework\Mock\Converter\ConverterMock;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;

class ChainTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = $this->createConverter(Chain::class, [
            'converters' => [
                $this->createConverter(ConverterMock::class),
                $this->createConverter(ConverterMock::class),
            ],
        ]);

        $value = $converter->convert('notAnonymized');
        $this->assertSame('test_test_notAnonymized', $value);
    }

    /**
     * Assert that an exception is thrown when the parameter "converters" is not set.
     */
    public function testConvertersNotSet(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(Chain::class);
    }

    /**
     * Assert that an exception is thrown when the parameter "converters" is not an array.
     */
    public function testInvalidConverters(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(Chain::class, ['converters' => 'notAnArray']);
    }
}
