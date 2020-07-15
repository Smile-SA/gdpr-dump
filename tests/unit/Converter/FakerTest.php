<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter;

use Faker\Factory as FakerFactory;
use InvalidArgumentException;
use Smile\GdprDump\Converter\Faker;
use Smile\GdprDump\Tests\Unit\TestCase;

class FakerTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $parameters = [
            'faker' => FakerFactory::create(),
            'formatter' => 'numberBetween',
            'arguments' => [1, 1],
        ];

        $converter = new Faker($parameters);

        $value = $converter->convert('notAnonymized');
        $this->assertSame(1, $value);
    }

    /**
     * Test the use of placeholder values.
     */
    public function testValuePlaceholder(): void
    {
        $parameters = [
            'faker' => FakerFactory::create(),
            'formatter' => 'numberBetween',
            'arguments' => ['{{value}}', '{{value}}'],
        ];

        $converter = new Faker($parameters);

        $value = $converter->convert(1);
        $this->assertSame(1, $value);
    }

    /**
     * Assert that an exception is thrown when the Faker provider is not set.
     */
    public function testProviderNotSet(): void
    {
        $parameters = ['formatter' => 'safeEmail'];
        $this->expectException(InvalidArgumentException::class);
        new Faker($parameters);
    }

    /**
     * Assert that an exception is thrown when the Faker formatter is not set.
     */
    public function testFormatterNotSet(): void
    {
        $parameters = ['faker' => FakerFactory::create()];
        $this->expectException(InvalidArgumentException::class);
        new Faker($parameters);
    }
}
