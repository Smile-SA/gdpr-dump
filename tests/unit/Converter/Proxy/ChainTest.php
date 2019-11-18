<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Proxy;

use Smile\GdprDump\Converter\Proxy\Chain;
use Smile\GdprDump\Tests\Framework\Mock\Converter\ConverterMock;
use Smile\GdprDump\Tests\Unit\TestCase;

class ChainTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter()
    {
        $parameters = [
            'converters' => [
                new ConverterMock(),
                new ConverterMock(),
            ],
        ];

        $converter = new Chain($parameters);

        $value = $converter->convert('notAnonymized');
        $this->assertSame('test_test_notAnonymized', $value);
    }

    /**
     * Assert that an exception is thrown when the parameter "converters" is not set.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testConvertersNotSet()
    {
        new Chain([]);
    }

    /**
     * Assert that an exception is thrown when the parameter "converters" is not an array.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testInvalidConverters()
    {
        new Chain(['converters' => 'notAnArray']);
    }
}
