<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Proxy;

use Smile\GdprDump\Converter\Proxy\FromContext;
use Smile\GdprDump\Tests\Unit\TestCase;

class NumberBetweenTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter()
    {
        $converter = new FromContext(['key' => 'row_data.email']);
        $context = ['row_data' => ['email' => 'test@example.org']];

        $value = $converter->convert('value', $context);
        $this->assertSame($context['row_data']['email'], $value);
    }

    /**
     * Assert that an exception is thrown when the parameter "key" is not set.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testKeyNotSet()
    {
        new FromContext([]);
    }

    /**
     * Assert that an exception is thrown when the parameter "key" is empty.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testEmptyKey()
    {
        new FromContext(['key' => '']);
    }
}
