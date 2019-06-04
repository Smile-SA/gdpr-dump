<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Converter\Randomizer;

use Smile\GdprDump\Converter\Randomizer\RandomizeText;
use Smile\GdprDump\Tests\TestCase;

class RandomizeTextTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter()
    {
        $converter = new RandomizeText();

        $value = $converter->convert('user1');
        $this->assertNotContains('user1', $value);
    }

    /**
     * Test the converter with a custom character replacement string.
     */
    public function testCustomReplacements()
    {
        $converter = new RandomizeText(['replacements' => 'a']);

        $value = $converter->convert('user1');
        $this->assertSame('aaaaa', $value);
    }
}
