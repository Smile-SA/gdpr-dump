<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Converter\Setter;

use Smile\GdprDump\Converter\Setter\SetSuffix;
use Smile\GdprDump\Tests\TestCase;

class SetSuffixTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter()
    {
        $converter = new SetSuffix(['suffix' => '_test']);

        $value = $converter->convert('value');
        $this->assertSame('value_test', $value);
    }

    /**
     * Assert that an exception is thrown when the suffix is not set.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testSuffixNotSet()
    {
        new SetSuffix();
    }
}
