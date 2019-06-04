<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Converter\Randomizer;

use Smile\GdprDump\Converter\Randomizer\RandomizeEmail;
use Smile\GdprDump\Tests\TestCase;

class RandomizeEmailTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter()
    {
        $converter = new RandomizeEmail(['domains' => ['example.org']]);

        $value = $converter->convert('user1@gmail.com');
        $this->assertNotContains('user1', $value);
        $this->assertNotContains('@gmail.com', $value);
        $this->assertStringEndsWith('@example.org', $value);
    }

    /**
     * Test the converter with a custom character replacement string.
     */
    public function testCustomReplacements()
    {
        $converter = new RandomizeEmail(['replacements' => 'a', 'domains' => ['example.org']]);

        $value = $converter->convert('user1@example.org');
        $this->assertSame('aaaaa@example.org', $value);
    }
}
