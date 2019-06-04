<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Converter\Proxy;

use Smile\GdprDump\Converter\Proxy\SerializedData;
use Smile\GdprDump\Tests\Converter\Dummy;
use Smile\GdprDump\Tests\TestCase;

class SerializedDataTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter()
    {
        $parameters = [
            'converters' => [
                'customer.firstname' => new Dummy(),
                'customer.lastname' => new Dummy(),
                'customer.notExists' => new Dummy(), // should not trigger an exception
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
            'converters' => ['email' => new Dummy()]
        ];

        $converter = new SerializedData($parameters);

        $value = $converter->convert($serializedData);
        $this->assertSame($serializedData, $value);
    }

    /**
     * Check if an exception is thrown when the converters are not set.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testConvertersNotSet()
    {
        new SerializedData([]);
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
