<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Proxy;

use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Converter\Proxy\FromContext;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;

class FromContextTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = $this->createConverter(FromContext::class, ['key' => 'row_data.email']);
        $context = ['row_data' => ['email' => 'test@example.org']];

        $value = $converter->convert('value', $context);
        $this->assertSame($context['row_data']['email'], $value);
    }

    /**
     * Assert that an exception is thrown when the parameter "key" is not set.
     */
    public function testKeyNotSet(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(FromContext::class);
    }

    /**
     * Assert that an exception is thrown when the parameter "key" is empty.
     */
    public function testEmptyKey(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(FromContext::class, ['key' => '']);
    }
}
