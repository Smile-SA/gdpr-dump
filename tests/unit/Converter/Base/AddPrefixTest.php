<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Base;

use InvalidArgumentException;
use Smile\GdprDump\Converter\Base\AddPrefix;
use Smile\GdprDump\Tests\Unit\TestCase;

class AddPrefixTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = new AddPrefix(['prefix' => 'test_']);

        $value = $converter->convert('value');
        $this->assertSame('test_value', $value);
    }

    /**
     * Assert that an exception is thrown when the prefix is not set.
     */
    public function testPrefixNotSet(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new AddPrefix();
    }
}
