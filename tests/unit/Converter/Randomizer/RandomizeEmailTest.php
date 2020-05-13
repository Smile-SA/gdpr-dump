<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Randomizer;

use Smile\GdprDump\Converter\Randomizer\RandomizeEmail;
use Smile\GdprDump\Tests\Unit\TestCase;
use UnexpectedValueException;

class RandomizeEmailTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter()
    {
        $converter = new RandomizeEmail(['domains' => ['example.org']]);

        $value = $converter->convert('user1@gmail.com');
        $this->assertNotContains('user1', $value);
        $this->assertNotContains('@gmail.com', $value);
        $this->assertStringEndsWith('@example.org', $value);
    }

    /**
     * Test the converter with a custom character replacement string.
     */
    public function testCustomReplacements()
    {
        $converter = new RandomizeEmail(['replacements' => 'a', 'domains' => ['example.org']]);

        $value = $converter->convert('user1@example.org');
        $this->assertSame('aaaaa@example.org', $value);
    }

    /**
     * Assert that an exception is thrown when the parameter "domains" is empty.
     */
    public function testEmptyDomains()
    {
        $this->expectException(UnexpectedValueException::class);
        new RandomizeEmail(['domains' => []]);
    }

    /**
     * Assert that an exception is thrown when the parameter "domains" is not an array.
     */
    public function testInvalidDomains()
    {
        $this->expectException(UnexpectedValueException::class);
        new RandomizeEmail(['domains' => 'invalid']);
    }
}
