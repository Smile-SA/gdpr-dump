<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Transformer;

use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Converter\Transformer\Replace;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;

class ReplaceTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = $this->createConverter(Replace::class, [
            'search' => 'bar',
            'replacement' => 'baz',
        ]);

        $value = $converter->convert(null);
        $this->assertSame('', $value);

        $value = $converter->convert('foofoofoo');
        $this->assertSame('foofoofoo', $value);

        $value = $converter->convert('foobarbaz');
        $this->assertSame('foobazbaz', $value);

        $value = $converter->convert('foobarbarbaz');
        $this->assertSame('foobazbazbaz', $value);

        $value = $converter->convert('foobarBarbaz');
        $this->assertSame('foobazBarbaz', $value);
    }

    /**
     * Assert that an exception is thrown when the string to search is empty.
     */
    public function testEmptySearch(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(Replace::class, [
            'search' => null,
            'replacement' => 'baz',
        ]);
    }
}
