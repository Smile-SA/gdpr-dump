<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Setter;

use Smile\GdprDump\Converter\Setter\SetNull;
use Smile\GdprDump\Tests\Unit\TestCase;

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
