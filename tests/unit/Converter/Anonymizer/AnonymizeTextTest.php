<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Anonymizer;

use Smile\GdprDump\Converter\Anonymizer\AnonymizeText;
use Smile\GdprDump\Tests\Unit\TestCase;
use UnexpectedValueException;

class AnonymizeTextTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = new AnonymizeText();

        $value = $converter->convert('');
        $this->assertSame('', $value);

        $value = $converter->convert('John Doe');
        $this->assertSame('J*** D**', $value);

        $value = $converter->convert('John_Doe');
        $this->assertSame('J***_D**', $value);

        $value = $converter->convert('John.Doe');
        $this->assertSame('J***.D**', $value);

        $value = $converter->convert('John-Doe');
        $this->assertSame('J*******', $value);

        $value = $converter->convert('  John Doe  ');
        $this->assertSame('  J*** D**  ', $value);
    }

    /**
     * Test the converter with an UTF-8 encoded value.
     */
    public function testEncoding(): void
    {
        $converter = new AnonymizeText();

        $value = $converter->convert('àà éé èè üü øø');
        $this->assertSame('à* é* è* ü* ø*', $value);

        $value = $converter->convert('汉字 한글 漢字');
        $this->assertSame('汉* 한* 漢*', $value);
    }

    /**
     * Test the converter with a minimum length per word.
     */
    public function testMinWordLength(): void
    {
        $converter = new AnonymizeText(['min_word_length' => 4]);

        $value = $converter->convert('John Doe');
        $this->assertSame('J*** D***', $value);

        $value = $converter->convert('  John Doe  ');
        $this->assertSame('  J*** D***  ', $value);
    }

    /**
     * Test the converter with a custom replacement character.
     */
    public function testCustomReplacement(): void
    {
        $converter = new AnonymizeText(['replacement' => 'x']);

        $value = $converter->convert('John Doe');
        $this->assertSame('Jxxx Dxx', $value);
    }

    /**
     * Test the converter with custom delimiter characters.
     */
    public function testCustomDelimiters(): void
    {
        $converter = new AnonymizeText(['delimiters' => ['%', '/']]);

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
        $converter = new AnonymizeText(['delimiters' => []]);

        foreach (['John Doe', 'John_Doe', 'John.Doe'] as $value) {
            $this->assertSame('J*******', $converter->convert($value));
        }
    }

    /**
     * Assert that an exception is thrown when the parameter "delimiters" is not an array.
     */
    public function testInvalidDelimiters(): void
    {
        $this->expectException(UnexpectedValueException::class);
        new AnonymizeText(['delimiters' => 'invalid']);
    }
}
