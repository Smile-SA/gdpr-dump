<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Randomizer;

use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Converter\Randomizer\RandomizeEmail;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;

class RandomizeEmailTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = $this->createConverter(RandomizeEmail::class, ['domains' => ['example.org']]);

        $value = $converter->convert(null);
        $this->assertSame('', $value);

        $value = $converter->convert('user1@gmail.com');
        $this->assertStringNotContainsString('user1', $value);
        $this->assertStringNotContainsString('@gmail.com', $value);
        $this->assertStringEndsWith('@example.org', $value);
    }

    /**
     * Test the converter with a custom min length.
     */
    public function testCustomLength(): void
    {
        $converter = $this->createConverter(RandomizeEmail::class, [
            'domains' => ['example.org'],
            'min_length' => 10,
        ]);

        $value = $converter->convert('user1@example.org');
        $this->assertStringEndsWith('@example.org', $value);

        $parts = explode('@', $value);
        $this->assertSame(10, strlen($parts[0]));
    }

    /**
     * Test the converter with a custom character replacement string.
     */
    public function testCustomReplacements(): void
    {
        $converter = $this->createConverter(RandomizeEmail::class, [
            'domains' => ['example.org'],
            'replacements' => 'a',
        ]);

        $value = $converter->convert('user1@example.org');
        $this->assertSame('aaaaa@example.org', $value);
    }

    /**
     * Assert that an exception is thrown when the parameter "domains" is empty.
     */
    public function testEmptyDomains(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(RandomizeEmail::class, ['domains' => []]);
    }

    /**
     * Assert that an exception is thrown when the parameter "domains" is not an array.
     */
    public function testInvalidDomains(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(RandomizeEmail::class, ['domains' => 'invalid']);
    }
}
