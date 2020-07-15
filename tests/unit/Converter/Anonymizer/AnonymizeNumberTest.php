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
}
