<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Proxy;

use InvalidArgumentException;
use Smile\GdprDump\Converter\Proxy\JsonData;
use Smile\GdprDump\Tests\Framework\Mock\Converter\ConverterMock;
use Smile\GdprDump\Tests\Unit\TestCase;
use UnexpectedValueException;

class JsonDataTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $parameters = [
            'converters' => [
                'customer.firstname' => new ConverterMock(),
                'customer.lastname' => new ConverterMock(),
                'customer.not_exists' => new ConverterMock(), // should not trigger an exception
            ],
        ];

        $converter = new JsonData($parameters);

        $value = $converter->convert($this->getJsonData());
        $this->assertSame($this->getExpectedData(), $value);
    }

    /**
     * Check if the converter ignores the value when it is not a JSON-encoded array.
     */
    public function testInvalidJsonData(): void
    {
        $jsonData = json_encode('stringValue');

        $parameters = [
            'converters' => ['address' => new ConverterMock()],
        ];

        $converter = new JsonData($parameters);

        $value = $converter->convert($jsonData);
        $this->assertSame($jsonData, $value);
    }

    /**
     * Assert that an exception is thrown when the converters are not set.
     */
    public function testConvertersNotSet(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new JsonData([]);
    }

    /**
     * Assert that an exception is thrown when the parameter "converters" is empty.
     */
    public function testEmptyConverters(): void
    {
        $this->expectException(UnexpectedValueException::class);
        new JsonData(['converters' => []]);
    }

    /**
     * Assert that an exception is thrown when the parameter "converters" is not an array.
     */
    public function testInvalidConverters(): void
    {
        $this->expectException(UnexpectedValueException::class);
        new JsonData(['converters' => 'notAnArray']);
    }

    /**
     * Get the JSON data to anonymize.
     *
     * @return string
     */
    private function getJsonData(): string
    {
        return json_encode([
            'customer' => [
                'firstname' => 'John',
                'lastname' => 'Doe',
            ],
        ]);
    }

    /**
     * Get the expected anonymized data.
     *
     * @return string
     */
    private function getExpectedData(): string
    {
        return json_encode([
            'customer' => [
                'firstname' => 'test_John',
                'lastname' => 'test_Doe',
            ],
        ]);
    }
}
