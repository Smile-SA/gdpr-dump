<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Tests\Converter\Proxy;

use PHPUnit\Framework\TestCase;
use Smile\Anonymizer\Converter\Anonymizer\ObfuscateEmail;

class ObfuscateEmailTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter()
    {
        $converter = new ObfuscateEmail(['domains' => ['example.org']]);

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
        $converter = new ObfuscateEmail(['replacements' => 'a', 'domains' => ['example.org']]);

        $value = $converter->convert('user1@example.org');
        $this->assertSame('aaaaa@example.org', $value);
    }
}
