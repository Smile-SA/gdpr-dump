<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Proxy;

use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Converter\Proxy\SerializedData;
use Smile\GdprDump\Tests\Framework\Mock\Converter\ConverterMock;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;

class SerializedDataTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = $this->createConverter(SerializedData::class, [
            'converters' => [
                'customer.firstname' => $this->createConverter(ConverterMock::class),
                'customer.lastname' => $this->createConverter(ConverterMock::class),
                'customer.not_exists' => $this->createConverter(ConverterMock::class), // must not trigger an exception
            ],
        ]);

        // Values that can't be decoded are returned as-is
        $value = $converter->convert(null);
        $this->assertNull($value);

        $value = $converter->convert($this->getSerializedData());
        $this->assertSame($this->getExpectedData(), $value);
    }

    /**
     * Check if the converter ignores the value when it is not a JSON-encoded array.
     */
    public function testInvalidJsonData(): void
    {
        $serializedData = serialize('stringValue');

        $converter = $this->createConverter(SerializedData::class, [
            'converters' => [
                'address' => $this->createConverter(ConverterMock::class),
            ],
        ]);

        $value = $converter->convert($serializedData);
        $this->assertSame($serializedData, $value);
    }

    /**
     * Assert that an exception is thrown when the converters are not set.
     */
    public function testConvertersNotSet(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(SerializedData::class);
    }

    /**
     * Assert that an exception is thrown when the parameter "converters" is empty.
     */
    public function testEmptyConverters(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(SerializedData::class, ['converters' => []]);
    }

    /**
     * Assert that an exception is thrown when the parameter "converters" is not an array.
     */
    public function testInvalidConverters(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(SerializedData::class, ['converters' => 'notAnArray']);
    }

    /**
     * Get the serialized data to anonymize.
     */
    private function getSerializedData(): string
    {
        return serialize([
            'customer' => [
                'firstname' => 'John',
                'lastname' => 'Doe',
            ],
        ]);
    }

    /**
     * Get the expected anonymized data.
     */
    private function getExpectedData(): string
    {
        return serialize([
            'customer' => [
                'firstname' => 'test_John',
                'lastname' => 'test_Doe',
            ],
        ]);
    }
}
