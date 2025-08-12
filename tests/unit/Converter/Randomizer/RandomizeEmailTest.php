<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Randomizer;

use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Converter\Randomizer\RandomizeEmail;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;

final class RandomizeEmailTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = $this->createConverter(RandomizeEmail::class);

        $value = $converter->convert(null);
        $this->assertSame('', $value);

        $email = $this->randomUsername();
        $value = $converter->convert($email);
        $this->assertEmailIsConverted($value, $email);

        $email = $this->randomEmail();
        $value = $converter->convert($email);
        $this->assertEmailIsConverted($value, $email);
    }

    /**
     * Test the converter with a custom min length.
     */
    public function testCustomLength(): void
    {
        $converter = $this->createConverter(RandomizeEmail::class, ['min_length' => 10]);

        $email = 'user1@acme.com';
        $value = $converter->convert($email);
        $this->assertEmailIsConverted($value, $email);

        $parts = explode('@', $value);
        $this->assertSame(10, strlen($parts[0]));
    }

    /**
     * Test the converter with a custom character replacement string.
     */
    public function testCustomReplacements(): void
    {
        $converter = $this->createConverter(RandomizeEmail::class, ['replacements' => 'a']);

        $email = 'user2@acme.com';
        $value = $converter->convert($email);
        $this->assertEmailIsConverted(
            $value,
            $email,
            callback: fn (string $username) => $this->assertSame('aaaaa', $username)
        );
    }

    /**
     * Test the converter with a custom domain.
     */
    public function testCustomDomain(): void
    {
        $expectedDomains = ['acme.com'];
        $converter = $this->createConverter(RandomizeEmail::class, ['domains' => $expectedDomains]);

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
        $this->createConverter(RandomizeEmail::class, ['domains' => []]);
    }

    /**
     * Assert that an exception is thrown when the parameter "domains" is not an array.
     */
    public function testInvalidDomains(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConverter(RandomizeEmail::class, ['domains' => 'invalid']);
    }
}
