<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Randomizer;

use Smile\GdprDump\Converter\Hash\HashText;
use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Tests\Unit\Converter\DumpContextAwareInterface;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;

final class HashTextTest extends TestCase implements DumpContextAwareInterface
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->getDumpContext()->secret = 'test_secret';
    }

    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = $this->createConverter(HashText::class);

        $value = $converter->convert(null);
        $this->assertSame('', $value);

        $value = $converter->convert('user1');
        $this->assertIsString($value);
        $this->assertValueIsHashed($value, 'user1');
    }

    /**
     * Test the converter with a custom length.
     */
    public function testCustomLength(): void
    {
        $lengths = [28, 56]; // Min and max possible lengthsfor sha224 algorithm

        foreach ($lengths as $length) {
            $converter = $this->createConverter(HashText::class, ['length' => $length]);
            $value = $converter->convert('user2');
            $this->assertIsString($value);
            $this->assertValueIsHashed($value, 'user2', $length);
        }
    }

    /**
     * Test the converter with a custom length set to the minimum amount possible (28 characters for sha224).
     */
    public function testMinLength(): void
    {
        $minLength = 28;
        $converter = $this->createConverter(HashText::class, ['length' => $minLength]);
        $value = $converter->convert('user3');
        $this->assertIsString($value);
        $this->assertValueIsHashed($value, 'user3', $minLength);
    }

    /**
     * Assert that an exception is thrown when the length exceeds the max length of the hash (56 characters for sha224).
     */
    public function testLengthTooHigh(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(HashText::class, ['length' => 57]);
    }

    /**
     * Assert that an exception is thrown when the length deceeds the min length of the hash (28 characters for sha224).
     */
    public function testLengthTooLow(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(HashText::class, ['length' => 27]);
    }

    /**
     * Test the converter with a custom algorithm.
     */
    public function testCustomAlgorithm(): void
    {
        $algorithm = 'sha1';
        $expectedLength = 10;

        $converter = $this->createConverter(HashText::class, ['algorithm' => $algorithm]);
        $value = $converter->convert('user4');
        $this->assertIsString($value);
        $this->assertValueIsHashed($value, 'user4', $expectedLength, $algorithm);
    }

    /**
     * Assert that an exception is thrown when the algorithm is not supported.
     */
    public function testInvalidAlgorithm(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(HashText::class, ['algorithm' => 'not_exists']);
    }
}
