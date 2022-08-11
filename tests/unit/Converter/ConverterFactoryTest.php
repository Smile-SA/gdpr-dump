<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter;

use RuntimeException;
use Smile\GdprDump\Converter\ConverterFactory;
use Smile\GdprDump\Converter\ConverterResolver;
use Smile\GdprDump\Converter\Proxy\Cache;
use Smile\GdprDump\Converter\Proxy\Chain;
use Smile\GdprDump\Converter\Proxy\Conditional;
use Smile\GdprDump\Converter\Proxy\Faker;
use Smile\GdprDump\Converter\Proxy\Unique;
use Smile\GdprDump\Faker\FakerService;
use Smile\GdprDump\Tests\Framework\Mock\Converter\ConverterMock;
use Smile\GdprDump\Tests\Unit\TestCase;
use UnexpectedValueException;

class ConverterFactoryTest extends TestCase
{
    /**
     * Test the converter creation from an array definition.
     */
    public function testConverterCreation(): void
    {
        $factory = $this->createFactory();

        $converter = $factory->create(['converter' => ConverterMock::class, 'parameters' => ['prefix' => '']]);
        $this->assertInstanceOf(ConverterMock::class, $converter);
    }

    /**
     * Test the creation of a Faker converter.
     */
    public function testFakerConverter(): void
    {
        $factory = $this->createFactory();

        $converter = $factory->create(['converter' => 'faker', 'parameters' => ['formatter' => 'safeEmail']]);
        $this->assertInstanceOf(Faker::class, $converter);
    }

    /**
     * Test the creation of a unique converter.
     */
    public function testUniqueConverter(): void
    {
        $factory = $this->createFactory();

        $converter = $factory->create(['converter' => ConverterMock::class, 'unique' => true]);
        $this->assertInstanceOf(Unique::class, $converter);

        $converter = $factory->create(['converter' => ConverterMock::class, 'unique' => false]);
        $this->assertInstanceOf(ConverterMock::class, $converter);
    }

    /**
     * Test the creation of a conditional converter.
     */
    public function testConditionConverter(): void
    {
        $factory = $this->createFactory();

        $converter = $factory->create(['converter' => ConverterMock::class, 'condition' => '{{id}} === 1']);
        $this->assertInstanceOf(Conditional::class, $converter);
    }

    /**
     * Test the creation of a cache converter.
     */
    public function testCacheConverter(): void
    {
        $factory = $this->createFactory();

        $converter = $factory->create(['converter' => ConverterMock::class, 'cache_key' => 'test']);
        $this->assertInstanceOf(Cache::class, $converter);
    }

    /**
     * Test the creation of nested converters.
     */
    public function testNestedConverters(): void
    {
        $factory = $this->createFactory();

        $converter = $factory->create([
            'converter' => Chain::class,
            'parameters' => [
                'converters' => [
                    ['converter' => ConverterMock::class],
                    ['converter' => ConverterMock::class],
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
        $factory = $this->createFactory();
        $this->expectException(UnexpectedValueException::class);
        $factory->create(['converter' => null]);
    }

    /**
     * Assert that an exception is thrown when the converter is not set.
     */
    public function testConverterNotSet(): void
    {
        $factory = $this->createFactory();
        $this->expectException(UnexpectedValueException::class);
        $factory->create([]);
    }

    /**
     * Assert that an exception is thrown when the converter is not defined.
     */
    public function testConverterNotDefined(): void
    {
        $factory = $this->createFactory();
        $this->expectException(RuntimeException::class);
        $factory->create(['converter' => 'notExists']);
    }

    /**
     * Assert that an exception is thrown when the parameter "parameters" is not an array.
     */
    public function testParametersNotAnArray(): void
    {
        $factory = $this->createFactory();
        $this->expectException(UnexpectedValueException::class);
        $factory->create(['converter' => ConverterMock::class, 'parameters' => '']);
    }

    /**
     * Assert that an exception is thrown when the parameter "converter" is used,
     * but the value is not a converter definition.
     */
    public function testConverterParameterMalformed(): void
    {
        $factory = $this->createFactory();
        $this->expectException(UnexpectedValueException::class);
        $factory->create(['converter' => ConverterMock::class, 'parameters' => ['converter' => null]]);
    }

    /**
     * Assert that an exception is thrown when the parameter "converters" is used,
     * but the value is not an array.
     */
    public function testConvertersParameterNotAnArray(): void
    {
        $factory = $this->createFactory();
        $this->expectException(UnexpectedValueException::class);
        $factory->create(['converter' => ConverterMock::class, 'parameters' => ['converters' => null]]);
    }

    /**
     * Assert that an exception is thrown when the parameter "converters" is used,
     * but the value is not an array of converter definition.
     */
    public function testConvertersParameterMalformed(): void
    {
        $factory = $this->createFactory();
        $this->expectException(UnexpectedValueException::class);
        $factory->create(['converter' => ConverterMock::class, 'parameters' => ['converters' => [null]]]);
    }

    /**
     * Create a converter factory object.
     */
    private function createFactory(): ConverterFactory
    {
        $resolver = new ConverterResolver();
        $resolver->addPath('Smile\\GdprDump\\Converter\\', dirname(__DIR__, 3) . '/src/Converter');

        return new ConverterFactory($resolver, new FakerService());
    }
}
