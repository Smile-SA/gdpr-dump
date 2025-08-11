<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Randomizer;

use Smile\GdprDump\Converter\Hash\HashEmail;
use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Tests\Unit\Converter\DumpContextAwareInterface;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;

final class HashEmailTest extends TestCase implements DumpContextAwareInterface
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
        $converter = $this->createConverter(HashEmail::class);

        $value = $converter->convert(null);
        $this->assertSame('', $value);

        $email = 'a';
        $value = $converter->convert($email);
        $this->assertIsString($value);
        $this->assertEmailIsHashed($value, $email);

        $email = $this->randomEmail();
        $value = $converter->convert($email);
        $this->assertIsString($value);
        $this->assertEmailIsHashed($value, $email);
    }

    /**
     * Test the converter with a custom length.
     */
    public function testCustomLength(): void
    {
        $lengths = [28, 56]; // Min and max possible lengthsfor sha224 algorithm

        foreach ($lengths as $length) {
            $email = $this->randomEmail();
            $converter = $this->createConverter(HashEmail::class, ['length' => $length]);
            $value = $converter->convert($email);
            $this->assertIsString($value);
            $this->assertEmailIsHashed($value, $email, $length);
        }
    }

    /**
     * Assert that an exception is thrown when the length exceeds the max length of the hash (56 characters for sha224).
     */
    public function testLengthTooHigh(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(HashEmail::class, ['length' => 57]);
    }

    /**
     * Assert that an exception is thrown when the length deceeds the min length of the hash (28 characters for sha224).
     */
    public function testLengthTooLow(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(HashEmail::class, ['length' => 27]);
    }

    /**
     * Test the converter with a custom algorithm.
     */
    public function testCustomAlgorithm(): void
    {
        $email = $this->randomEmail();
        $algorithm = 'sha1';
        $expectedLength = 10;

        $converter = $this->createConverter(HashEmail::class, ['algorithm' => $algorithm]);
        $value = $converter->convert($email);
        $this->assertIsString($value);
        $this->assertEmailIsHashed($value, $email, $expectedLength, $algorithm);
    }

    /**
     * Assert that an exception is thrown when the algorithm is not supported.
     */
    public function testInvalidAlgorithm(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(HashEmail::class, ['algorithm' => 'not_exists']);
    }

    /**
     * Test the converter with a custom domain.
     */
    public function testCustomDomain(): void
    {
        $expectedDomains = ['acme.com'];
        $converter = $this->createConverter(HashEmail::class, ['domains' => $expectedDomains]);

        $email = $this->randomEmail();
        $value = $converter->convert($email);
        $this->assertIsString($value);
        $this->assertEmailIsHashed($value, $email, 56 / 2, 'sha224', $expectedDomains);
    }

    /**
     * Assert that an exception is thrown when the parameter "domains" is empty.
     */
    public function testEmptyDomains(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(HashEmail::class, ['domains' => []]);
    }

    /**
     * Assert that an exception is thrown when the parameter "domains" is not an array.
     */
    public function testInvalidDomains(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(HashEmail::class, ['domains' => 'invalid']);
    }

    /**
     * Assert that an email was properly hash.
     */
    private function assertEmailIsHashed(
        string $actual,
        string $original,
        int $expectedLength = 56 / 2,
        string $algorithm = 'sha224',
        array $expectedDomains = ['example.com', 'example.net', 'example.org'],
    ): void {
        $callback = fn (string $actualUsername, string $originalUsername) => $this
            ->assertValueIsHashed($actualUsername, $originalUsername, $expectedLength, $algorithm);

        $this->assertEmailIsConverted($actual, $original, $expectedDomains, $callback);
    }
}
