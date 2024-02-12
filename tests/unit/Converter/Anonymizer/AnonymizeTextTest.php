<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Anonymizer;

use Smile\GdprDump\Converter\Anonymizer\AnonymizeText;
use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;

class AnonymizeTextTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = $this->createConverter(AnonymizeText::class);

        $value = $converter->convert(null);
        $this->assertSame('', $value);

        $value = $converter->convert('a');
        $this->assertSame('a**', $value);

        $value = $converter->convert('a.b');
        $this->assertSame('a**.b**', $value);

        $value = $converter->convert('John Doe');
        $this->assertSame('J*** D**', $value);

        foreach ([' ', '_', '-', '.'] as $separator) {
            $value = $converter->convert('John' . $separator . 'Doe');
            $this->assertSame('J***' . $separator . 'D**', $value);
        }
    }

    /**
     * Test the converter with a UTF-8 encoded value.
     */
    public function testEncoding(): void
    {
        $converter = $this->createConverter(AnonymizeText::class);

        $value = $converter->convert('àà éé èè üü øø');
        $this->assertSame('à** é** è** ü** ø**', $value);

        $value = $converter->convert('汉字 한글 漢字');
        $this->assertSame('汉** 한** 漢**', $value);
    }

    /**
     * Test the converter with a minimum length per word.
     */
    public function testMinWordLength(): void
    {
        $converter = $this->createConverter(AnonymizeText::class, ['min_word_length' => 4]);

        $value = $converter->convert('John Doe');
        $this->assertSame('J*** D***', $value);

        $value = $converter->convert('  John Doe  ');
        $this->assertSame('  J*** D***  ', $value);
    }

    /**
     * Assert that an exception is thrown when the parameter "min_word_length" is empty.
     */
    public function testEmptyMinWordLength(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(AnonymizeText::class, ['min_word_length' => null]);
    }

    /**
     * Test the converter with a custom replacement character.
     */
    public function testCustomReplacement(): void
    {
        $converter = $this->createConverter(AnonymizeText::class, ['replacement' => 'x']);

        $value = $converter->convert('John Doe');
        $this->assertSame('Jxxx Dxx', $value);
    }

    /**
     * Assert that an exception is thrown when the parameter "replacement" is empty.
     */
    public function testEmptyReplacement(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(AnonymizeText::class, ['replacement' => '']);
    }

    /**
     * Test the converter with custom delimiter characters.
     */
    public function testCustomDelimiters(): void
    {
        $converter = $this->createConverter(AnonymizeText::class, ['delimiters' => ['%', '/']]);

        $value = $converter->convert('John Doe');
        $this->assertSame('J*******', $value);

        $value = $converter->convert('John%Doe');
        $this->assertSame('J***%D**', $value);

        $value = $converter->convert('John/Doe');
        $this->assertSame('J***/D**', $value);
    }

    /**
     * Test the converter with no delimiter characters.
     */
    public function testEmptyDelimiters(): void
    {
        $converter = $this->createConverter(AnonymizeText::class, ['delimiters' => []]);

        foreach (['John Doe', 'John_Doe', 'John.Doe'] as $value) {
            $this->assertSame('J*******', $converter->convert($value));
        }
    }

    /**
     * Assert that an exception is thrown when the parameter "delimiters" is not an array.
     */
    public function testInvalidDelimiters(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(AnonymizeText::class, ['delimiters' => 'invalid']);
    }
}
