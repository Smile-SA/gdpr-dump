<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Anonymizer;

use Smile\GdprDump\Converter\Anonymizer\AnonymizeEmail;
use Smile\GdprDump\Tests\Unit\TestCase;
use UnexpectedValueException;

class AnonymizeEmailTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = new AnonymizeEmail(['domains' => ['example.org']]);

        $value = $converter->convert('');
        $this->assertSame('', $value);

        $value = $converter->convert('user1@gmail.com');
        $this->assertSame('u****@example.org', $value);

        $value = $converter->convert('john.doe@gmail.com');
        $this->assertSame('j***.d**@example.org', $value);
    }

    /**
     * Test the converter with a minimum length per word.
     */
    public function testMinWordLength(): void
    {
        $converter = new AnonymizeEmail(['domains' => ['example.org'], 'min_word_length' => 4]);

        $value = $converter->convert('john.doe@gmail.com');
        $this->assertSame('j***.d***@example.org', $value);
    }

    /**
     * Test the converter with a custom replacement character.
     */
    public function testCustomReplacement(): void
    {
        $converter = new AnonymizeEmail(['domains' => ['example.org'], 'replacement' => 'x']);

        $value = $converter->convert('john.doe@gmail.com');
        $this->assertSame('jxxx.dxx@example.org', $value);
    }

    /**
     * Test the converter with custom delimiter characters.
     */
    public function testCustomDelimiters(): void
    {
        $converter = new AnonymizeEmail(['domains' => ['example.org'], 'delimiters' => ['%', '/']]);

        $value = $converter->convert('john.doe@gmail.com');
        $this->assertSame('j*******@example.org', $value);

        $value = $converter->convert('john%doe@gmail.com');
        $this->assertSame('j***%d**@example.org', $value);

        $value = $converter->convert('john/doe@gmail.com');
        $this->assertSame('j***/d**@example.org', $value);
    }

    /**
     * Test the converter with no delimiter characters.
     */
    public function testEmptyDelimiters(): void
    {
        $converter = new AnonymizeEmail(['domains' => ['example.org'], 'delimiters' => []]);

        $value = $converter->convert('john.doe@gmail.com');
        $this->assertSame('j*******@example.org', $value);
    }

    /**
     * Assert that an exception is thrown when the parameter "delimiters" is not an array.
     */
    public function testInvalidDelimiters(): void
    {
        $this->expectException(UnexpectedValueException::class);
        new AnonymizeEmail(['delimiters' => 'invalid']);
    }

    /**
     * Assert that an exception is thrown when the parameter "domains" is empty.
     */
    public function testEmptyDomains(): void
    {
        $this->expectException(UnexpectedValueException::class);
        new AnonymizeEmail(['domains' => []]);
    }

    /**
     * Assert that an exception is thrown when the parameter "domains" is not an array.
     */
    public function testInvalidDomains(): void
    {
        $this->expectException(UnexpectedValueException::class);
        new AnonymizeEmail(['domains' => 'invalid']);
    }
}
