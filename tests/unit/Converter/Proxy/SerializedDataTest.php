<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Proxy;

use InvalidArgumentException;
use Smile\GdprDump\Converter\Proxy\SerializedData;
use Smile\GdprDump\Tests\Framework\Mock\Converter\ConverterMock;
use Smile\GdprDump\Tests\Unit\TestCase;
use UnexpectedValueException;

class SerializedDataTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter()
    {
        $parameters = [
            'converters' => [
                'customer.firstname' => new ConverterMock(),
                'customer.lastname' => new ConverterMock(),
                'customer.not_exists' => new ConverterMock(), // should not trigger an exception
            ],
        ];

        $converter = new SerializedData($parameters);

        $value = $converter->convert($this->getSerializedData());
        $this->assertSame($this->getExpectedData(), $value);
    }

    /**
     * Check if the converter ignores the value when it is not a JSON-encoded array.
     */
    public function testInvalidJsonData()
    {
        $serializedData = serialize('stringValue');

        $parameters = [
            'converters' => ['address' => new ConverterMock()],
        ];

        $converter = new SerializedData($parameters);

        $value = $converter->convert($serializedData);
        $this->assertSame($serializedData, $value);
    }

    /**
     * Assert that an exception is thrown when the converters are not set.
     */
    public function testConvertersNotSet()
    {
        $this->expectException(InvalidArgumentException::class);
        new SerializedData([]);
    }

    /**
     * Assert that an exception is thrown when the parameter "converters" is empty.
     */
    public function testEmptyConverters()
    {
        $this->expectException(UnexpectedValueException::class);
        new SerializedData(['converters' => []]);
    }

    /**
     * Assert that an exception is thrown when the parameter "converters" is not an array.
     */
    public function testInvalidConverters()
    {
        $this->expectException(UnexpectedValueException::class);
        new SerializedData(['converters' => 'notAnArray']);
    }

    /**
     * Get the serialized data to anonymize.
     *
     * @return string
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
     *
     * @return string
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
