<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Transformer;

use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Converter\Transformer\PrependText;
use Smile\GdprDump\Tests\Unit\TestCase;

class PrependTextTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = new PrependText(['value' => 'test_']);

        // Empty value: no prefix added
        $value = $converter->convert(null);
        $this->assertSame('', $value);

        $value = $converter->convert('user1');
        $this->assertSame('test_user1', $value);
    }

    /**
     * Assert that an exception is thrown when the prefix is not set.
     */
    public function testPrefixNotSet(): void
    {
        $this->expectException(ValidationException::class);
        new PrependText();
    }
}
