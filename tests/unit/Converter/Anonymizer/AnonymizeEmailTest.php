<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Anonymizer;

use Smile\GdprDump\Converter\Anonymizer\AnonymizeEmail;
use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;

class AnonymizeEmailTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = $this->createConverter(AnonymizeEmail::class, ['domains' => ['example.org']]);

        $value = $converter->convert(null);
        $this->assertSame('', $value);

        $value = $converter->convert('a');
        $this->assertSame('a**', $value);

        $value = $converter->convert('a@gmail.com');
        $this->assertSame('a**@example.org', $value);

        $value = $converter->convert('user1');
        $this->assertSame('u****', $value);

        $value = $converter->convert('user1@gmail.com');
        $this->assertSame('u****@example.org', $value);

        $value = $converter->convert('john.doe@gmail.com');
        $this->assertSame('j***.d**@example.org', $value);
    }

    /**
     * Test the converter with a UTF-8 encoded value.
     */
    public function testEncoding(): void
    {
        $converter = $this->createConverter(AnonymizeEmail::class, ['domains' => ['example.org']]);

        $value = $converter->convert('àà.éé.èè.üü.øø@gmail.com');
        $this->assertSame('à**.é**.è**.ü**.ø**@example.org', $value);

        $value = $converter->convert('汉字.한글.漢字@gmail.com');
        $this->assertSame('汉**.한**.漢**@example.org', $value);
    }

    /**
     * Test the converter with a minimum length per word.
     */
    public function testMinWordLength(): void
    {
        $converter = $this->createConverter(AnonymizeEmail::class, [
            'domains' => ['example.org'],
            'min_word_length' => 4,
        ]);

        $value = $converter->convert('john.doe@gmail.com');
        $this->assertSame('j***.d***@example.org', $value);
    }

    /**
     * Assert that an exception is thrown when the parameter "min_word_length" is empty.
     */
    public function testEmptyMinWordLength(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(AnonymizeEmail::class, ['min_word_length' => null]);
    }

    /**
     * Test the converter with a custom replacement character.
     */
    public function testCustomReplacement(): void
    {
        $converter = $this->createConverter(AnonymizeEmail::class, [
            'domains' => ['example.org'],
            'replacement' => 'x',
        ]);

        $value = $converter->convert('john.doe@gmail.com');
        $this->assertSame('jxxx.dxx@example.org', $value);
    }

    /**
     * Assert that an exception is thrown when the parameter "replacement" is empty.
     */
    public function testEmptyReplacement(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(AnonymizeEmail::class, ['replacement' => '']);
    }

    /**
     * Test the converter with custom delimiter characters.
     */
    public function testCustomDelimiters(): void
    {
        $converter = $this->createConverter(AnonymizeEmail::class, [
            'domains' => ['example.org'],
            'delimiters' => ['%', '/'],
        ]);

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
        $converter = $this->createConverter(AnonymizeEmail::class, [
            'domains' => ['example.org'],
            'delimiters' => [],
        ]);

        $value = $converter->convert('john.doe@gmail.com');
        $this->assertSame('j*******@example.org', $value);
    }

    /**
     * Assert that an exception is thrown when the parameter "delimiters" is not an array.
     */
    public function testInvalidDelimiters(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(AnonymizeEmail::class, ['delimiters' => 'invalid']);
    }

    /**
     * Assert that an exception is thrown when the parameter "domains" is empty.
     */
    public function testEmptyDomains(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(AnonymizeEmail::class, ['domains' => []]);
    }

    /**
     * Assert that an exception is thrown when the parameter "domains" is not an array.
     */
    public function testInvalidDomains(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(AnonymizeEmail::class, ['domains' => 'invalid']);
    }
}
