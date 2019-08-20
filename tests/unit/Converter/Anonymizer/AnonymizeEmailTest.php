<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Anonymizer;

use Smile\GdprDump\Converter\Anonymizer\AnonymizeEmail;
use Smile\GdprDump\Tests\Unit\TestCase;

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
