<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Transformer;

use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Converter\Transformer\AppendText;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;

class AppendTextTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = $this->createConverter(AppendText::class, ['value' => '_test']);

        $value = $converter->convert(null);
        $this->assertSame('', $value);

        $value = $converter->convert('user1');
        $this->assertSame('user1_test', $value);
    }

    /**
     * Assert that an exception is thrown when the suffix is empty.
     */
    public function testEmptySuffix(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(AppendText::class, ['suffix' => '']);
    }
}
