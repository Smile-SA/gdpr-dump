<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Anonymizer;

use Smile\GdprDump\Converter\Anonymizer\AnonymizeNumber;
use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;

class AnonymizeNumberTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = $this->createConverter(AnonymizeNumber::class);

        $value = $converter->convert(null);
        $this->assertSame('', $value);

        $value = $converter->convert('123456');
        $this->assertSame('1*****', $value);

        $value = $converter->convert('123-456');
        $this->assertSame('1**-4**', $value);

        $value = $converter->convert('user123');
        $this->assertSame('user1**', $value);

        $value = $converter->convert('1000 > 100 > 10 > 1');
        $this->assertSame('1*** > 1** > 1* > 1', $value);

        $value = $converter->convert('+33601020304');
        $this->assertSame('+3**********', $value);
    }

    /**
     * Test the converter with a UTF-8 encoded value.
     */
    public function testEncoding(): void
    {
        $converter = $this->createConverter(AnonymizeNumber::class);

        $value = $converter->convert('àà10 éé20 èè30 üü40 øø50');
        $this->assertSame('àà1* éé2* èè3* üü4* øø5*', $value);

        $value = $converter->convert('汉字10 한글20 漢字30');
        $this->assertSame('汉字1* 한글2* 漢字3*', $value);
    }

    /**
     * Test the converter with a minimum length per number.
     */
    public function testMinNumberLength(): void
    {
        $converter = $this->createConverter(AnonymizeNumber::class, ['min_number_length' => 4]);

        foreach (['1', '12', '123', '1234'] as $value) {
            $this->assertSame('1***', $converter->convert($value));
        }

        $value = $converter->convert('123 456 123456');
        $this->assertSame('1*** 4*** 1*****', $value);
    }

    /**
     * Assert that an exception is thrown when the parameter "min_number_length" is empty.
     */
    public function testEmptyMinNumberLength(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(AnonymizeNumber::class, ['min_number_length' => null]);
    }

    /**
     * Test the converter with a custom replacement character.
     */
    public function testCustomReplacement(): void
    {
        $converter = $this->createConverter(AnonymizeNumber::class, ['replacement' => 'x']);

        $value = $converter->convert('123-456');
        $this->assertSame('1xx-4xx', $value);
    }

    /**
     * Assert that an exception is thrown when the parameter "replacement" is empty.
     */
    public function testEmptyReplacement(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(AnonymizeNumber::class, ['replacement' => '']);
    }
}
