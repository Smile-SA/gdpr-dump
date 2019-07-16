<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Converter\Setter;

use Smile\GdprDump\Converter\Setter\SetPrefix;
use Smile\GdprDump\Tests\TestCase;

class SetPrefixTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter()
    {
        $converter = new SetPrefix(['prefix' => 'test_']);

        $value = $converter->convert('value');
        $this->assertSame('test_value', $value);
    }

    /**
     * Assert that an exception is thrown when the prefix is not set.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testPrefixNotSet()
    {
        new SetPrefix();
    }
}
