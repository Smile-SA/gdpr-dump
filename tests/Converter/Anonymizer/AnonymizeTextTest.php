<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Tests\Converter\Anonymizer;

use PHPUnit\Framework\TestCase;
use Smile\Anonymizer\Converter\Anonymizer\AnonymizeText;

class AnonymizeTextTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter()
    {
        $converter = new AnonymizeText();

        $value = $converter->convert('user1');
        $this->assertSame('u****', $value);

        $value = $converter->convert('John Doe');
        $this->assertSame('J*** D**', $value);

        $value = $converter->convert('John_Doe');
        $this->assertSame('J***_D**', $value);

        $value = $converter->convert('john.doe');
        $this->assertSame('j***.d**', $value);

        $value = $converter->convert('John-Doe');
        $this->assertSame('J*******', $value);
    }
}
