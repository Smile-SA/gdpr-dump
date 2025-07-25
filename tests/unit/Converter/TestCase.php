<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter;

use DateTime;
use RuntimeException;
use Smile\GdprDump\Converter\ConditionBuilder;
use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Proxy\Conditional;
use Smile\GdprDump\Converter\Proxy\Faker;
use Smile\GdprDump\Faker\FakerService;
use Smile\GdprDump\Tests\Unit\TestCase as UnitTestCase;

abstract class TestCase extends UnitTestCase
{
    /**
     * Create a converter.
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
            default => new $className(),
        };

        $converter->setParameters($parameters);

        return $converter;
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
}
