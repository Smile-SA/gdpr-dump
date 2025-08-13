<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Converters;

use Smile\GdprDump\Converter\Converters\SetNull;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;

final class SetNullTest extends TestCase
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
