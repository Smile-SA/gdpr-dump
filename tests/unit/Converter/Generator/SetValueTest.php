<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Generator;

use Smile\GdprDump\Converter\Generator\SetValue;
use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Tests\Unit\TestCase;

class SetValueTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = new SetValue(['value' => 1,]);

        $value = $converter->convert(null);
        $this->assertSame(1, $value);

        $value = $converter->convert('');
        $this->assertSame(1, $value);

        $value = $converter->convert('notAnonymized');
        $this->assertSame(1, $value);
    }

    /**
     * Assert that an exception is thrown when the value is not set.
     */
    public function testValueNotSet(): void
    {
        $this->expectException(ValidationException::class);
        new SetValue([]);
    }
}
