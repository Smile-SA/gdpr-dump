<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Base;

use Smile\GdprDump\Converter\Base\ToLower;
use Smile\GdprDump\Tests\Unit\TestCase;

class ToLowerTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter()
    {
        $converter = new ToLower();

        $value = $converter->convert('VaLuE');
        $this->assertSame('value', $value);
    }
}
