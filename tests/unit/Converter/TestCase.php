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

class TestCase extends UnitTestCase
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

        $converter = new $className();
        $converter->setParameters($parameters);

        return $converter;
    }

    /**
     * Create a conditional converter.
     */
    public function createConditionalConverter(array $parameters = []): ConverterInterface
    {
        $converter = new Conditional(new ConditionBuilder());
        $converter->setParameters($parameters);

        return $converter;
    }

    /**
     * Create a Faker converter.
     */
    public function createFakerConverter(array $parameters = []): ConverterInterface
    {
        $converter = new Faker(new FakerService());
        $converter->setParameters($parameters);

        return $converter;
    }
}
