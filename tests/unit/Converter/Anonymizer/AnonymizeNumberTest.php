<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Anonymizer;

use Smile\GdprDump\Converter\Anonymizer\AnonymizeNumber;
use Smile\GdprDump\Tests\Unit\TestCase;

class AnonymizeNumberTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = new AnonymizeNumber();

        $value = $converter->convert('');
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
     * Test the converter with a minimum length per number.
     */
    public function testMinNumberLength(): void
    {
        $converter = new AnonymizeNumber(['min_number_length' => 4]);

        foreach (['1', '12', '123', '1234'] as $value) {
            $this->assertSame('1***', $converter->convert($value));
        }

        $value = $converter->convert('123 456 123456');
        $this->assertSame('1*** 4*** 1*****', $value);
    }

    /**
     * Test the converter with a custom replacement character.
     */
    public function testCustomReplacement(): void
    {
        $converter = new AnonymizeNumber(['replacement' => 'x']);

        $value = $converter->convert('123-456');
        $this->assertSame('1xx-4xx', $value);
    }
}
