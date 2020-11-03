<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Generator;

use Smile\GdprDump\Converter\Generator\RandomText;
use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Tests\Unit\TestCase;

class RandomTextTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = new RandomText();

        $value = $converter->convert(null);
        $this->assertNotNUll($value);

        $value = $converter->convert('user1');
        $this->assertStringNotContainsString('user1', $value);
        $this->assertGreaterThanOrEqual(3, strlen($value));
    }

    /**
     * Test the converter with a minimum and maximum length.
     */
    public function testCustomLength(): void
    {
        $converter = new RandomText(['min_length' => 20, 'max_length' => 20]);

        $value = $converter->convert('user1');
        $this->assertSame(20, strlen($value));
    }

    /**
     * Test the converter with a custom character list.
     */
    public function testCustomCharacters(): void
    {
        $converter = new RandomText(['characters' => 'a', 'min_length' => 5, 'max_length' => 5]);

        $value = $converter->convert('user1');
        $this->assertSame('aaaaa', $value);
    }

    /**
     * Assert that an exception is thrown when the parameter "characters" is empty.
     */
    public function testEmptyCharacters(): void
    {
        $this->expectException(ValidationException::class);
        new RandomText(['characters' => '']);
    }
}
