<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Transformer;

use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Converter\Transformer\AddPrefix;
use Smile\GdprDump\Tests\Unit\TestCase;

class AddPrefixTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = new AddPrefix(['prefix' => 'test_']);

        // Empty value: no prefix added
        $value = $converter->convert(null);
        $this->assertSame('', $value);

        $value = $converter->convert('value');
        $this->assertSame('test_value', $value);
    }

    /**
     * Assert that an exception is thrown when the prefix is not set.
     */
    public function testPrefixNotSet(): void
    {
        $this->expectException(ValidationException::class);
        new AddPrefix();
    }
}
