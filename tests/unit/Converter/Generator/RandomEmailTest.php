<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Generator;

use Smile\GdprDump\Converter\Generator\RandomEmail;
use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;

final class RandomEmailTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = $this->createConverter(RandomEmail::class);

        $value = $converter->convert(null);
        $this->assertNotNull($value);

        $email = $this->randomUsername();
        $value = $converter->convert($email);
        $this->assertEmailIsConverted($value, $email);

        $email = $this->randomEmail();
        $value = $converter->convert($email);
        $this->assertEmailIsConverted($value, $email);
    }

    /**
     * Test the converter with a minimum and maximum length.
     */
    public function testCustomLength(): void
    {
        $converter = $this->createConverter(RandomEmail::class, ['min_length' => 10, 'max_length' => 10]);

        $email = $this->randomEmail();
        $value = $converter->convert($email);
        $this->assertEmailIsConverted($value, $email);

        $parts = explode('@', $value);
        $this->assertSame(10, strlen($parts[0]));
    }

    /**
     * Test the converter with a custom character list.
     */
    public function testCustomCharacters(): void
    {
        $converter = $this->createConverter(RandomEmail::class, ['characters' => 'a', 'max_length' => 3]);

        $email = 'user1@acme.com';
        $value = $converter->convert($email);
        $this->assertEmailIsConverted(
            $value,
            $email,
            callback: fn (string $username) => $this->assertSame('aaa', $username)
        );
    }

    /**
     * Assert that an exception is thrown when the parameter "characters" is empty.
     */
    public function testEmptyCharacters(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(RandomEmail::class, ['characters' => '']);
    }

    /**
     * Test the converter with a custom domain.
     */
    public function testCustomDomain(): void
    {
        $expectedDomains = ['acme.com'];
        $converter = $this->createConverter(RandomEmail::class, ['domains' => $expectedDomains]);

        $email = $this->randomEmail();
        $value = $converter->convert($email);
        $this->assertEmailIsConverted($value, $email, $expectedDomains);
    }

    /**
     * Assert that an exception is thrown when the parameter "domains" is empty.
     */
    public function testEmptyDomains(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(RandomEmail::class, ['domains' => []]);
    }

    /**
     * Assert that an exception is thrown when the parameter "domains" is not an array.
     */
    public function testInvalidDomains(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(RandomEmail::class, ['domains' => 'invalid']);
    }
}
