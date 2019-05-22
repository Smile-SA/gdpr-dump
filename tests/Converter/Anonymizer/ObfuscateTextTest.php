<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Tests\Converter\Anonymizer;

use PHPUnit\Framework\TestCase;
use Smile\Anonymizer\Converter\Anonymizer\ObfuscateText;

class ObfuscateTextTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter()
    {
        $converter = new ObfuscateText();

        $value = $converter->convert('user1');
        $this->assertNotContains('user1', $value);
    }

    /**
     * Test the converter with a custom character replacement string.
     */
    public function testCustomReplacements()
    {
        $converter = new ObfuscateText(['replacements' => 'a']);

        $value = $converter->convert('user1');
        $this->assertSame('aaaaa', $value);
    }
}
