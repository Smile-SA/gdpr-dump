<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Tests\Converter\Setter;

use Smile\Anonymizer\Converter\Setter\SetNull;
use Smile\Anonymizer\Tests\TestCase;

class SetNullTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter()
    {
        $converter = new SetNull();

        $value = $converter->convert('notAnonymized');
        $this->assertNull($value);
    }
}
