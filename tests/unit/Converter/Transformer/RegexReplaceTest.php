<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Transformer;

use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Converter\Transformer\RegexReplace;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;

class RegexReplaceTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = $this->createConverter(RegexReplace::class, [
            'pattern' => '/[0-9]+/',
            'replacement' => 'bar',
        ]);

        $value = $converter->convert(null);
        $this->assertSame('', $value);

        $value = $converter->convert('foofoofoo');
        $this->assertSame('foofoofoo', $value);

        $value = $converter->convert('foo1020baz');
        $this->assertSame('foobarbaz', $value);

        $value = $converter->convert('foo1020baz3040baz');
        $this->assertSame('foobarbazbarbaz', $value);
    }

    /**
     * Test the "limit" parameter.
     */
    public function testLimitParameter(): void
    {
        $converter = $this->createConverter(RegexReplace::class, [
            'pattern' => '/[0-9]+/',
            'replacement' => 'bar',
            'limit' => 1,
        ]);

        $value = $converter->convert('foo1020baz3040baz');
        $this->assertSame('foobarbaz3040baz', $value);
    }

    /**
     * Assert that an exception is thrown when the pattern is empty.
     */
    public function testEmptyPattern(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(RegexReplace::class, [
            'pattern' => null,
            'replacement' => 'baz',
        ]);
    }
}
