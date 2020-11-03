<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Generator;

use Smile\GdprDump\Converter\Generator\RandomEmail;
use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Tests\Unit\TestCase;

class RandomEmailTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = new RandomEmail(['domains' => ['example.org']]);

        $value = $converter->convert(null);
        $this->assertNotNull($value);

        $value = $converter->convert('user1@gmail.com');
        $this->assertStringNotContainsString('user1', $value);
        $this->assertStringNotContainsString('@gmail.com', $value);
        $this->assertStringEndsWith('@example.org', $value);
    }

    /**
     * Test the converter with a minimum and maximum length.
     */
    public function testCustomLength(): void
    {
        $converter = new RandomEmail(['min_length' => 10, 'max_length' => 10, 'domains' => ['example.org']]);

        $value = $converter->convert('user1@example.org');
        $this->assertStringEndsWith('@example.org', $value);

        $parts = explode('@', $value);
        $this->assertSame(10, strlen($parts[0]));
    }

    /**
     * Test the converter with a custom character list.
     */
    public function testCustomCharacters(): void
    {
        $converter = new RandomEmail(['characters' => 'a', 'max_length' => 3, 'domains' => ['example.org']]);

        $value = $converter->convert('user1@example.org');
        $this->assertSame('aaa@example.org', $value);
    }

    /**
     * Assert that an exception is thrown when the parameter "characters" is empty.
     */
    public function testEmptyCharacters(): void
    {
        $this->expectException(ValidationException::class);
        new RandomEmail(['characters' => '']);
    }

    /**
     * Assert that an exception is thrown when the parameter "domains" is empty.
     */
    public function testEmptyDomains(): void
    {
        $this->expectException(ValidationException::class);
        new RandomEmail(['domains' => []]);
    }

    /**
     * Assert that an exception is thrown when the parameter "domains" is not an array.
     */
    public function testInvalidDomains(): void
    {
        $this->expectException(ValidationException::class);
        new RandomEmail(['domains' => 'invalid']);
    }
}
