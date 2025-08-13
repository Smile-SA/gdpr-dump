<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Converters;

use Smile\GdprDump\Converter\Converters\RandomizeText;
use Smile\GdprDump\Converter\Exception\InvalidParameterException;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;

final class RandomizeTextTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = $this->createConverter(RandomizeText::class);

        $value = $converter->convert(null);
        $this->assertSame('', $value);

        $username = $this->randomUsername();
        $value = $converter->convert($username);
        $this->assertStringNotContainsString($username, $value);
    }

    /**
     * Test the converter with a custom min length.
     */
    public function testCustomLength(): void
    {
        $converter = $this->createConverter(RandomizeText::class, ['min_length' => 10]);

        $username = 'user1';
        $value = $converter->convert($username);
        $this->assertStringNotContainsString($username, $value);
        $this->assertSame(10, strlen($value));
    }

    /**
     * Test the converter with a custom character replacement string.
     */
    public function testCustomReplacements(): void
    {
        $converter = $this->createConverter(RandomizeText::class, ['replacements' => 'a']);

        $value = $converter->convert('user2');
        $this->assertSame('aaaaa', $value);
    }

    /**
     * Assert that an exception is thrown when the parameter "replacements" is empty.
     */
    public function testEmptyReplacements(): void
    {
        $this->expectException(InvalidParameterException::class);
        $this->createConverter(RandomizeText::class, ['replacements' => '']);
    }
}
