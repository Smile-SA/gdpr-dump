<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Tests\Converter\Proxy;

use PHPUnit\Framework\TestCase;
use Smile\Anonymizer\Converter\ConverterInterface;
use Smile\Anonymizer\Converter\Proxy\Chain;
use Smile\Anonymizer\Tests\Converter\Dummy;

class ChainTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter()
    {
        $parameters = [
            'converters' => [
                new Dummy(),
                new Dummy(),
            ],
        ];

        $converter = new Chain($parameters);

        $value = $converter->convert('notAnonymized');
        $this->assertSame('test_test_notAnonymized', $value);
    }

    /**
     * Test if an exception is thrown when the converter chain is not set.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testConvertersNotSet()
    {
        new Chain([]);
    }
}
