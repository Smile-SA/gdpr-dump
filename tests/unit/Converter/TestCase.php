<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter;

use DateTime;
use Faker\Factory;
use RuntimeException;
use Smile\GdprDump\Converter\Converter;
use Smile\GdprDump\Converter\IsConfigurable;
use Smile\GdprDump\Converter\IsContextAware;
use Smile\GdprDump\Converter\IsFakerAware;
use Smile\GdprDump\Dumper\DumpContext;
use Smile\GdprDump\Faker\LazyGenerator;
use Smile\GdprDump\Tests\Unit\TestCase as UnitTestCase;

abstract class TestCase extends UnitTestCase
{
    private DumpContext $dumpContext;

    protected function setUp(): void
    {
        if ($this instanceof DumpContextAware) {
            $this->dumpContext = new DumpContext();
        }
    }

    protected function tearDown(): void
    {
        if ($this instanceof DumpContextAware) {
            unset($this->dumpContext);
        }
    }

    /**
     * Create a converter.
     *
     * @template T of Converter
     * @param class-string<T> $className
     * @return T
     */
    public function createConverter(string $className, array $parameters = []): Converter
    {
        // @phpstan-ignore function.alreadyNarrowedType
        if (!is_a($className, Converter::class, true)) {
            throw new RuntimeException(
                sprintf('The class "%s" does not implement %s.', $className, Converter::class)
            );
        }

        $converter = new $className();

        if ($converter instanceof IsContextAware) {
            $converter->setDumpContext($this->getDumpContext());
        }

        if ($converter instanceof IsFakerAware) {
            $converter->setFaker((new LazyGenerator(Factory::DEFAULT_LOCALE))->getGenerator());
        }

        if ($converter instanceof IsConfigurable) {
            $converter->setParameters($parameters);
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
        if (!$this instanceof DumpContextAware) {
            throw new RuntimeException('Please implement DumpContextAware to access the dump context object');
        }

        return $this->dumpContext;
    }
}
