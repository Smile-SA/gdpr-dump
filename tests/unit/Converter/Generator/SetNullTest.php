<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Generator;

use Smile\GdprDump\Converter\Generator\SetNull;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;

class SetNullTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = $this->createConverter(SetNull::class);

        $value = $converter->convert(null);
        $this->assertNull($value);

        $value = $converter->convert('');
        $this->assertNull($value);

        $value = $converter->convert('notAnonymized');
        $this->assertNull($value);
    }
}
