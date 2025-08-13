<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Converters;

use Smile\GdprDump\Converter\Converters\Faker;
use Smile\GdprDump\Converter\Exception\InvalidParameterException;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;

final class FakerTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = $this->createConverter(Faker::class, [
            'formatter' => 'numberBetween',
            'arguments' => [1, 1],
        ]);

        $value = $converter->convert('notAnonymized');
        $this->assertSame(1, $value);
    }

    /**
     * Test the use of placeholder values.
     */
    public function testValuePlaceholder(): void
    {
        $converter = $this->createConverter(Faker::class, [
            'formatter' => 'numberBetween',
            'arguments' => ['{{value}}', '{{value}}'],
        ]);

        $value = $converter->convert(1);
        $this->assertSame(1, $value);
    }

    /**
     * Assert that an exception is thrown when the Faker formatter is not set.
     */
    public function testFormatterNotSet(): void
    {
        $this->expectException(InvalidParameterException::class);
        $this->createConverter(Faker::class);
    }

    /**
     * Assert that an exception is thrown when the Faker formatter is not defined.
     */
    public function testInvalidFormatter(): void
    {
        $this->expectException(InvalidParameterException::class);
        $this->createConverter(Faker::class, [
            'formatter' => 'doesNotExist',
            'arguments' => [1, 1],
        ]);
    }
}
