<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Tests\Converter\Proxy;

use Smile\Anonymizer\Converter\Proxy\Unique;
use Smile\Anonymizer\Converter\Setter\SetNull;
use Smile\Anonymizer\Tests\Converter\Dummy;
use Smile\Anonymizer\Tests\TestCase;

class UniqueTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter()
    {
        $parameters = [
            'converter' => new Dummy(),
        ];

        $converter = new Unique($parameters);
        $value = $converter->convert('1');
        $this->assertSame('test_1', $value);
    }

    /**
     * Test if NULL values are ignored by the converter.
     */
    public function testNullValuesIgnored()
    {
        $parameters = [
            'converter' => new SetNull(),
        ];

        $converter = new Unique($parameters);

        $value = $converter->convert('1');
        $this->assertNull($value);

        // Should not throw an exception, the unique converter ignores values converted to null
        $converter->convert('1');
        $converter->convert('1');
    }

    /**
     * Test if an exception is thrown when the converter fails to generate a unique value.
     *
     * @expectedException \OverflowException
     */
    public function testFailedUniqueValue()
    {
        $parameters = [
            'converter' => new Dummy(),
        ];

        $converter = new Unique($parameters);
        $converter->convert('1');
        $converter->convert('1');
    }

    /**
     * Test if an exception is thrown when the converter is not set.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testConverterNotSet()
    {
        new Unique([]);
    }
}
