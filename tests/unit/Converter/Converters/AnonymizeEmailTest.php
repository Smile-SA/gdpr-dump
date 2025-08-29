<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Converters;

use Smile\GdprDump\Converter\Converters\AnonymizeEmail;
use Smile\GdprDump\Converter\Exception\InvalidParameterException;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;

final class AnonymizeEmailTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter(): void
    {
        $converter = $this->createConverter(AnonymizeEmail::class);

        $value = $converter->convert(null);
        $this->assertSame('', $value);

        $email = 'a';
        $value = $converter->convert($email);
        $this->assertEmailIsAnonymized('a**', $value, $email);

        $email = 'b@acme.com';
        $value = $converter->convert($email);
        $this->assertEmailIsAnonymized('b**', $value, $email);

        $email = 'user1';
        $value = $converter->convert($email);
        $this->assertEmailIsAnonymized('u****', $value, $email);

        $email = 'user2@acme.com';
        $value = $converter->convert($email);
        $this->assertEmailIsAnonymized('u****', $value, $email);

        $email = 'john.doe@acme.com';
        $value = $converter->convert($email);
        $this->assertEmailIsAnonymized('j***.d**', $value, $email);
    }

    /**
     * Test the converter with a UTF-8 encoded value.
     */
    public function testEncoding(): void
    {
        $converter = $this->createConverter(AnonymizeEmail::class);

        $email = 'àà.éé.èè.üü.øø@acme.com';
        $value = $converter->convert($email);
        $this->assertEmailIsAnonymized('à**.é**.è**.ü**.ø**', $value, $email);

        $email = '汉字.한글.漢字@acme.com';
        $value = $converter->convert($email);
        $this->assertEmailIsAnonymized('汉**.한**.漢**', $value, $email);
    }

    /**
     * Test the converter with a minimum length per word.
     */
    public function testMinWordLength(): void
    {
        $converter = $this->createConverter(AnonymizeEmail::class, ['min_word_length' => 4]);

        $email = 'john.doe@acme.com';
        $value = $converter->convert($email);
        $this->assertEmailIsAnonymized('j***.d***', $value, $email);
    }

    /**
     * Assert that an exception is thrown when the parameter "min_word_length" is empty.
     */
    public function testEmptyMinWordLength(): void
    {
        $this->expectException(InvalidParameterException::class);
        $this->createConverter(AnonymizeEmail::class, ['min_word_length' => null]);
    }

    /**
     * Test the converter with a custom replacement character.
     */
    public function testCustomReplacement(): void
    {
        $converter = $this->createConverter(AnonymizeEmail::class, ['replacement' => 'x']);

        $email = 'john.doe@acme.com';
        $value = $converter->convert($email);
        $this->assertEmailIsAnonymized('jxxx.dxx', $value, $email);
    }

    /**
     * Assert that an exception is thrown when the parameter "replacement" is empty.
     */
    public function testEmptyReplacement(): void
    {
        $this->expectException(InvalidParameterException::class);
        $this->createConverter(AnonymizeEmail::class, ['replacement' => '']);
    }

    /**
     * Test the converter with custom delimiter characters.
     */
    public function testCustomDelimiters(): void
    {
        $converter = $this->createConverter(AnonymizeEmail::class, ['delimiters' => ['%', '/']]);

        $email = 'john.doe@acme.com';
        $value = $converter->convert($email);
        $this->assertEmailIsAnonymized('j*******', $value, $email);

        $email = 'john%doe@acme.com';
        $value = $converter->convert($email);
        $this->assertEmailIsAnonymized('j***%d**', $value, $email);

        $email = 'john/doe@acme.com';
        $value = $converter->convert($email);
        $this->assertEmailIsAnonymized('j***/d**', $value, $email);
    }

    /**
     * Test the converter with no delimiter characters.
     */
    public function testEmptyDelimiters(): void
    {
        $converter = $this->createConverter(AnonymizeEmail::class, ['delimiters' => []]);

        $email = 'john.doe@acme.com';
        $value = $converter->convert($email);
        $this->assertEmailIsAnonymized('j*******', $value, $email);
    }

    /**
     * Assert that an exception is thrown when the parameter "delimiters" is not an array.
     */
    public function testInvalidDelimiters(): void
    {
        $this->expectException(InvalidParameterException::class);
        $this->createConverter(AnonymizeEmail::class, ['delimiters' => 'invalid']);
    }

    /**
     * Test the converter with a custom domain.
     */
    public function testCustomDomain(): void
    {
        $expectedDomains = ['acme.com'];
        $converter = $this->createConverter(AnonymizeEmail::class, ['domains' => $expectedDomains]);

        $email = 'john.doe@acme.com';
        $value = $converter->convert($email);
        $this->assertEmailIsAnonymized('j***.d**', $value, $email, $expectedDomains);
    }

    /**
     * Assert that an exception is thrown when the parameter "domains" is empty.
     */
    public function testEmptyDomains(): void
    {
        $this->expectException(InvalidParameterException::class);
        $this->createConverter(AnonymizeEmail::class, ['domains' => []]);
    }

    /**
     * Assert that an exception is thrown when the parameter "domains" is not an array.
     */
    public function testInvalidDomains(): void
    {
        $this->expectException(InvalidParameterException::class);
        $this->createConverter(AnonymizeEmail::class, ['domains' => 'invalid']);
    }

    /**
     * Assert that an email is anonymized.
     */
    protected function assertEmailIsAnonymized(
        string $expectedUsername,
        string $actualEmail,
        string $originalEmail,
        array $expectedDomains = ['example.com', 'example.net', 'example.org'],
    ): void {
        $this->assertEmailIsConverted(
            $actualEmail,
            $originalEmail,
            $expectedDomains,
            fn (string $username) => $this->assertSame($username, $expectedUsername)
        );
    }
}
