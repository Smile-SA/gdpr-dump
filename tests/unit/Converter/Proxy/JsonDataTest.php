<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Proxy;

use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Converter\Proxy\JsonData;
use Smile\GdprDump\Tests\Framework\Mock\Converter\ConverterMock;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;

class JsonDataTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = $this->createConverter(JsonData::class, [
            'converters' => [
                'customer.firstname' => $this->createConverter(ConverterMock::class),
                'customer.lastname' => $this->createConverter(ConverterMock::class),
                'customer.not_exists' => $this->createConverter(ConverterMock::class), // must not trigger an exception
            ],
        ]);

        // Values that can't be decoded are returned as-is
        $value = $converter->convert(null);
        $this->assertNull($value);

        $value = $converter->convert($this->getJsonData());
        $this->assertJson($this->getExpectedData(), $value);
    }

    /**
     * Check if the converter ignores the value when it is not a JSON-encoded array.
     */
    public function testInvalidJsonData(): void
    {
        $jsonData = json_encode('stringValue');

        $converter = $this->createConverter(JsonData::class, [
            'converters' => [
                'address' => $this->createConverter(ConverterMock::class),
            ],
        ]);

        $value = $converter->convert($jsonData);
        $this->assertSame($jsonData, $value);
    }

    /**
     * Assert that an exception is thrown when the converters are not set.
     */
    public function testConvertersNotSet(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(JsonData::class);
    }

    /**
     * Assert that an exception is thrown when the parameter "converters" is empty.
     */
    public function testEmptyConverters(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(JsonData::class, ['converters' => []]);
    }

    /**
     * Assert that an exception is thrown when the parameter "converters" is not an array.
     */
    public function testInvalidConverters(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(JsonData::class, ['converters' => 'notAnArray']);
    }

    /**
     * Get the JSON data to anonymize.
     */
    private function getJsonData(): string
    {
        $data = json_encode([
            'customer' => [
                'firstname' => 'John',
                'lastname' => 'Doe',
            ],
        ]);

        $this->assertIsString($data);

        return $data;
    }

    /**
     * Get the expected anonymized data.
     */
    private function getExpectedData(): string
    {
        $data = json_encode([
            'customer' => [
                'firstname' => 'test_John',
                'lastname' => 'test_Doe',
            ],
        ]);

        $this->assertIsString($data);

        return $data;
    }
}
