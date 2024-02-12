<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Transformer;

use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Converter\Transformer\Hash;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;

class HashTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = $this->createConverter(Hash::class);

        // Empty value: value converted to an empty string
        $value = $converter->convert(null);
        $this->assertSame('', $value);

        $value = $converter->convert('user1');
        $this->assertSame(40, strlen($value));
    }

    /**
     * Test the converter with another algorithm than the default one.
     */
    public function testCustomAlgorithm(): void
    {
        $converter = $this->createConverter(Hash::class, ['algorithm' => 'sha256']);

        $value = $converter->convert('user1');
        $this->assertSame(64, strlen($value));
    }

    /**
     * Assert that an exception is thrown when the algorithm is invalid.
     */
    public function testInvalidAlgorithm(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(Hash::class, ['algorithm' => 'invalid']);
    }
}
