<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Tests\Converter\Anonymizer;

use PHPUnit\Framework\TestCase;
use Smile\Anonymizer\Converter\Anonymizer\AnonymizeEmail;

class AnonymizeEmailTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter()
    {
        $converter = new AnonymizeEmail(['domains' => ['example.org']]);

        $value = $converter->convert('user1@gmail.com');
        $this->assertSame('u****@example.org', $value);

        $value = $converter->convert('john.doe@gmail.com');
        $this->assertSame('j***.d**@example.org', $value);
    }
}
