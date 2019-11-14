<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Base;

use Smile\GdprDump\Converter\Base\ToUpper;
use Smile\GdprDump\Tests\Unit\TestCase;

class ToUpperTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter()
    {
        $converter = new ToUpper();

        $value = $converter->convert('VaLuE');
        $this->assertSame('VALUE', $value);
    }
}
