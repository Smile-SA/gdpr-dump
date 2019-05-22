<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Tests\Converter\Anonymizer;

use PHPUnit\Framework\TestCase;
use Smile\Anonymizer\Converter\Anonymizer\ObfuscateNumber;

class ObfuscateNumberTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter()
    {
        $converter = new ObfuscateNumber();

        $value = $converter->convert('+33601010101');
        $this->assertStringStartsWith('+', $value);
        $this->assertSame(12, strlen($value));

        // Assert that the part without the "+" is still a numeric value
        $valueWithoutPlus = substr($value, 1);
        $this->assertTrue(is_numeric($valueWithoutPlus));
        $this->assertNotSame('33601010101', $valueWithoutPlus);
    }
}
