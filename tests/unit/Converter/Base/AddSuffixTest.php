<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Base;

use InvalidArgumentException;
use Smile\GdprDump\Converter\Base\AddSuffix;
use Smile\GdprDump\Tests\Unit\TestCase;

class AddSuffixTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter()
    {
        $converter = new AddSuffix(['suffix' => '_test']);

        $value = $converter->convert('value');
        $this->assertSame('value_test', $value);
    }

    /**
     * Assert that an exception is thrown when the suffix is not set.
     */
    public function testSuffixNotSet()
    {
        $this->expectException(InvalidArgumentException::class);
        new AddSuffix();
    }
}
