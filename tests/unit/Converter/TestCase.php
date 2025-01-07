<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter;

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
}
