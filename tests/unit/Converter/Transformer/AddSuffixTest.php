<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Transformer;

use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Converter\Transformer\AddSuffix;
use Smile\GdprDump\Tests\Unit\TestCase;

class AddSuffixTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = new AddSuffix(['suffix' => '_test']);

        // Empty value: no suffix added
        $value = $converter->convert(null);
        $this->assertSame('', $value);

        $value = $converter->convert('value');
        $this->assertSame('value_test', $value);
    }

    /**
     * Assert that an exception is thrown when the suffix is not set.
     */
    public function testSuffixNotSet(): void
    {
        $this->expectException(ValidationException::class);
        new AddSuffix();
    }
}
