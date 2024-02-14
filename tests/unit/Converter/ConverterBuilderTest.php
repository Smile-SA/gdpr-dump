<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter;

use RuntimeException;
use Smile\GdprDump\Converter\ConverterBuilder;
use Smile\GdprDump\Converter\ConverterFactory;
use Smile\GdprDump\Converter\Proxy\Cache;
use Smile\GdprDump\Converter\Proxy\Chain;
use Smile\GdprDump\Converter\Proxy\Conditional;
use Smile\GdprDump\Converter\Proxy\Faker;
use Smile\GdprDump\Converter\Proxy\Unique;
use Smile\GdprDump\Tests\Framework\Mock\Converter\ConverterMock;
use UnexpectedValueException;

class ConverterBuilderTest extends TestCase
{
    /**
     * Test the converter creation from an array definition.
     */
    public function testConverterCreation(): void
    {
        $builder = $this->createBuilder();

        $converter = $builder->build([
            'converter' => 'mock',
            'parameters' => [
                'prefix' => '',
            ],
        ]);
        $this->assertInstanceOf(ConverterMock::class, $converter);
    }

    /**
     * Test the creation of a Faker converter.
     */
    public function testFakerConverter(): void
    {
        $builder = $this->createBuilder();

        $converter = $builder->build([
            'converter' => 'faker',
            'parameters' => [
                'formatter' => 'safeEmail',
            ],
        ]);
        $this->assertInstanceOf(Faker::class, $converter);
    }

    /**
     * Test the creation of a unique converter.
     */
    public function testUniqueConverter(): void
    {
        $builder = $this->createBuilder();

        $converter = $builder->build([
            'converter' => 'mock',
            'unique' => true,
        ]);
        $this->assertInstanceOf(Unique::class, $converter);

        $converter = $builder->build([
            'converter' => 'mock',
            'unique' => false,
        ]);
        $this->assertInstanceOf(ConverterMock::class, $converter);
    }

    /**
     * Test the creation of a conditional converter.
     */
    public function testConditionConverter(): void
    {
        $builder = $this->createBuilder();

        $converter = $builder->build([
            'converter' => 'mock',
            'condition' => '{{id}} === 1',
        ]);
        $this->assertInstanceOf(Conditional::class, $converter);
    }

    /**
     * Test the creation of a cache converter.
     */
    public function testCacheConverter(): void
    {
        $builder = $this->createBuilder();

        $converter = $builder->build([
            'converter' => 'mock',
            'cache_key' => 'test',
        ]);
        $this->assertInstanceOf(Cache::class, $converter);
    }

    /**
     * Test the creation of nested converters.
     */
    public function testNestedConverters(): void
    {
        $builder = $this->createBuilder();

        $converter = $builder->build([
            'converter' => 'chain',
            'parameters' => [
                'converters' => [
                    ['converter' => 'mock'],
                    ['converter' => 'mock'],
                ],
            ],
        ]);
        $this->assertInstanceOf(Chain::class, $converter);
    }

    /**
     * Assert that an exception is thrown when the converter is set but empty.
     */
    public function testEmptyConverter(): void
    {
        $builder = $this->createBuilder();
        $this->expectException(UnexpectedValueException::class);
        $builder->build(['converter' => null]);
    }

    /**
     * Assert that an exception is thrown when the converter is not set.
     */
    public function testConverterNotSet(): void
    {
        $builder = $this->createBuilder();
        $this->expectException(UnexpectedValueException::class);
        $builder->build([]);
    }

    /**
     * Assert that an exception is thrown when the converter is not defined.
     */
    public function testConverterNotDefined(): void
    {
        $builder = $this->createBuilder();
        $this->expectException(RuntimeException::class);
        $builder->build(['converter' => 'notExists']);
    }

    /**
     * Assert that an exception is thrown when the parameter "parameters" is not an array.
     */
    public function testParametersNotAnArray(): void
    {
        $builder = $this->createBuilder();
        $this->expectException(UnexpectedValueException::class);
        $builder->build([
            'converter' => 'mock',
            'parameters' => '',
        ]);
    }

    /**
     * Assert that an exception is thrown when the parameter "converter" is used,
     * but the value is not a converter definition.
     */
    public function testConverterParameterMalformed(): void
    {
        $builder = $this->createBuilder();
        $this->expectException(UnexpectedValueException::class);
        $builder->build([
            'converter' => 'mock',
            'parameters' => [
                'converter' => null,
            ],
        ]);
    }

    /**
     * Assert that an exception is thrown when the parameter "converters" is used,
     * but the value is not an array.
     */
    public function testConvertersParameterNotAnArray(): void
    {
        $builder = $this->createBuilder();
        $this->expectException(UnexpectedValueException::class);
        $builder->build([
            'converter' => 'mock',
            'parameters' => [
                'converters' => null,
            ],
        ]);
    }

    /**
     * Assert that an exception is thrown when the parameter "converters" is used,
     * but the value is not an array of converter definition.
     */
    public function testConvertersParameterMalformed(): void
    {
        $builder = $this->createBuilder();
        $this->expectException(UnexpectedValueException::class);
        $builder->build([
            'converter' => 'mock',
            'parameters' => [
                'converters' => [null],
            ],
        ]);
    }

    /**
     * Create a converter factory object.
     */
    private function createBuilder(): ConverterBuilder
    {
        $containerMock = $this->createMock(ConverterFactory::class);
        $containerMock
            ->method('create')
            ->will(
                $this->returnCallback(
                    fn (string $value, array $parameters) => match ($value) {
                        // Converters used in the context of this unit test
                        'cache' => $this->createConverter(Cache::class, $parameters),
                        'chain' => $this->createConverter(Chain::class, $parameters),
                        'conditional' => $this->createConditionalConverter($parameters),
                        'faker' => $this->createFakerConverter($parameters),
                        'mock' => $this->createConverter(ConverterMock::class, $parameters),
                        'notExists' => throw new RuntimeException($value),
                        'unique' => $this->createConverter(Unique::class, $parameters),
                        default => throw new UnexpectedValueException(
                            sprintf('The converter "%s" was not expected in this unit case.', $value)
                        ),
                    }
                )
            );

        return new ConverterBuilder($containerMock);
    }
}
