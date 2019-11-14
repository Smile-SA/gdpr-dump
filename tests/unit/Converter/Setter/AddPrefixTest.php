<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Setter;

use Smile\GdprDump\Converter\Setter\AddPrefix;
use Smile\GdprDump\Tests\Unit\TestCase;

class AddPrefixTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter()
    {
        $converter = new AddPrefix(['prefix' => 'test_']);

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
        new AddPrefix();
    }
}
