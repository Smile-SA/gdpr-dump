<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Converters;

use Smile\GdprDump\Converter\Converters\RandomText;
use Smile\GdprDump\Converter\Exception\InvalidParameterException;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;

final class RandomTextTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = $this->createConverter(RandomText::class);

        $value = $converter->convert(null);
        $this->assertNotSame('', $value);

        $username = $this->randomUsername();
        $value = $converter->convert($username);
        $this->assertStringNotContainsString($username, $value);

        $char = 'a';
        $value = $converter->convert($char);
        $this->assertGreaterThanOrEqual(3, strlen($value));
    }

    /**
     * Test the converter with a minimum and maximum length.
     */
    public function testCustomLength(): void
    {
        $converter = $this->createConverter(RandomText::class, ['min_length' => 20, 'max_length' => 20]);

        $username = 'user1';
        $value = $converter->convert($username);
        $this->assertStringNotContainsString($username, $value);
        $this->assertSame(20, strlen($value));
    }

    /**
     * Test the converter with a custom character list.
     */
    public function testCustomCharacters(): void
    {
        $converter = $this->createConverter(RandomText::class, [
            'characters' => 'a',
            'min_length' => 5,
            'max_length' => 5,
        ]);

        $value = $converter->convert('user2');
        $this->assertSame('aaaaa', $value);
    }

    /**
     * Assert that an exception is thrown when the parameter "characters" is empty.
     */
    public function testEmptyCharacters(): void
    {
        $this->expectException(InvalidParameterException::class);
        $this->createConverter(RandomText::class, ['characters' => '']);
    }
}
