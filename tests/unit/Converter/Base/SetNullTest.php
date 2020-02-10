<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Base;

use Smile\GdprDump\Converter\Base\SetNull;
use Smile\GdprDump\Tests\Unit\TestCase;

class SetNullTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = new SetNull();

        $value = $converter->convert('notAnonymized');
        $this->assertNull($value);
    }
}
