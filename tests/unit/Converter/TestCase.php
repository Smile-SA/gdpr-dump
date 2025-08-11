<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter;

use DateTime;
use RuntimeException;
use Smile\GdprDump\Converter\ConditionBuilder;
use Smile\GdprDump\Converter\ContextAwareInterface;
use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Proxy\Faker;
use Smile\GdprDump\Converter\Proxy\Internal\Conditional;
use Smile\GdprDump\Converter\Proxy\JsonData;
use Smile\GdprDump\Converter\Proxy\SerializedData;
use Smile\GdprDump\Dumper\DumpContext;
use Smile\GdprDump\Faker\FakerService;
use Smile\GdprDump\Tests\Unit\TestCase as UnitTestCase;
use Smile\GdprDump\Util\ArrayHelper;

abstract class TestCase extends UnitTestCase
{
    private DumpContext $dumpContext;

    protected function setUp(): void
    {
        if ($this instanceof DumpContextAwareInterface) {
            $this->dumpContext = new DumpContext();
        }
    }

    protected function tearDown(): void
    {
        if ($this instanceof DumpContextAwareInterface) {
            unset($this->dumpContext);
        }
    }

    /**
     * Create a converter.
     *
     * @template T
     * @param class-string<T> $className
     * @return T
     * @phpstan-ignore return.phpDocType (T is resolved at the beginning of the function)
     */
    public function createConverter(string $className, array $parameters = []): ConverterInterface
    {
        if (!is_a($className, ConverterInterface::class, true)) {
            throw new RuntimeException(
                sprintf('The class "%s" does not implement %s.', $className, ConverterInterface::class)
            );
        }

        $converter = match ($className) {
            Conditional::class => new Conditional(new ConditionBuilder()),
            Faker::class => new Faker(new FakerService()),
            JsonData::class => new JsonData(new ArrayHelper()),
            SerializedData::class => new SerializedData(new ArrayHelper()),
            default => new $className(),
        };

        $converter->setParameters($parameters);

        if ($converter instanceof ContextAwareInterface) {
            $converter->setDumpContext($this->getDumpContext());
        }

        return $converter;
    }

    /**
     * Assert that an email is converted (username and domain were changed).
     */
    protected function assertEmailIsConverted(
        string $actual,
        string $original,
        array $expectedDomains = ['example.com', 'example.net', 'example.org'],
        ?callable $callback = null,
    ): void {
        // Check for position of "@" character
        $originalSeparatorPos = strrpos($original, '@');

        // Domain validation
        if ($originalSeparatorPos !== false) {
            $actualSeparatorPos = strrpos($actual, '@');
            $this->assertNotFalse($actualSeparatorPos);

            $actualDomain = substr($actual, $actualSeparatorPos + 1);
            $this->assertTrue(in_array($actualDomain, $expectedDomains, true));
        } else {
            $originalSeparatorPos = null;
            $actualSeparatorPos = null;
        }

        // Username validation
        $actualUsername = substr($actual, 0, $actualSeparatorPos);
        $originalUsername = substr($original, 0, $originalSeparatorPos);
        $this->assertNotSame($originalUsername, $actualUsername);

        // Additional username validation (if the callback is defined)
        if ($callback) {
            $callback($actualUsername, $originalUsername);
        }
    }

    /**
     * Assert that a date is anonymized.
     */
    protected function assertDateIsAnonymized(string $anonymized, string $actual, string $format): void
    {
        $anonymizedDate = DateTime::createFromFormat($format, $anonymized);
        $actualDate = DateTime::createFromFormat($format, $actual);

        // Make sure that PHP didn't fail to create the dates
        $this->assertNotFalse($anonymizedDate);
        $this->assertNotFalse($actualDate);

        // The year must not have changed
        $this->assertSame($anonymizedDate->format('Y'), $actualDate->format('Y'));

        // The day and month must have been randomized
        $this->assertTrue(
            $anonymizedDate->format('n') !== $actualDate->format('n')
            || $anonymizedDate->format('j') !== $actualDate->format('j')
        );

        // The time must not have changed
        $this->assertSame($anonymizedDate->format('H:i:s'), $actualDate->format('H:i:s'));
    }

    /**
     * Assert that a date is randomized.
     */
    protected function assertDateIsRandomized(string $randomized, string $actual, string $format): void
    {
        $randomizedDate = DateTime::createFromFormat($format, $randomized);
        $actualDate = DateTime::createFromFormat($format, $actual);

        $this->assertTrue($randomizedDate !== $actualDate);
    }

    /**
     * Assert that a value was properly hash.
     *
     * With the default settings, the hash if generated with the sha224 algorithm,
     * and the value is truncated to half the size of the hash (24 chars instead of 56).
     */
    protected function assertValueIsHashed(
        string $actual,
        string $original,
        int $expectedLength = 56 / 2,
        string $algorithm = 'sha224',
    ): void {
        $hash = hash_hmac($algorithm, $original, $this->getDumpContext()->secret);

        // The converter is supposed to truncate the hash to the specified length
        $hash = substr($hash, 0, $expectedLength);

        $this->assertStringContainsString($hash, $actual);
        $this->assertSame($expectedLength, strlen($hash));
    }

    /**
     * Generate a random username.
     */
    protected function randomUsername(): string
    {
        return 'user_' . random_int(1, 999999);
    }

    /**
     * Generate a random email.
     */
    protected function randomEmail(): string
    {
        $domains = ['acme.com', 'acme.net', 'acme.org'];

        return $this->randomUsername() . '@' . $domains[random_int(0, 1)];
    }

    /**
     * Get the dump context object.
     */
    protected function getDumpContext(): DumpContext
    {
        if (!$this instanceof DumpContextAwareInterface) {
            throw new RuntimeException('Please implement DumpContextAwareInterface to access the dump context object');
        }

        return $this->dumpContext;
    }
}
