<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter;

use RuntimeException;
use Smile\GdprDump\Converter\ConditionBuilder;
use Smile\GdprDump\Converter\ConverterBuilder;
use Smile\GdprDump\Converter\ConverterFactory;
use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Proxy\Chain;
use Smile\GdprDump\Converter\Proxy\Faker;
use Smile\GdprDump\Converter\Proxy\Internal\Cache;
use Smile\GdprDump\Converter\Proxy\Internal\Conditional;
use Smile\GdprDump\Converter\Proxy\Internal\Unique;
use Smile\GdprDump\DependencyInjection\ConverterAliasResolver;
use Smile\GdprDump\Dumper\Config\Definition\ConverterConfig;
use Smile\GdprDump\Faker\FakerService;
use Smile\GdprDump\Tests\Framework\Mock\Converter\ConverterMock;
use Symfony\Component\DependencyInjection\Container;
use UnexpectedValueException;

final class ConverterBuilderTest extends TestCase implements DumpContextAwareInterface
{
    /**
     * Test the converter creation from an array definition.
     */
    public function testConverterCreation(): void
    {
        $converter = $this->buildConverter([
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
        $converter = $this->buildConverter([
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
        $converter = $this->buildConverter(['converter' => 'mock', 'unique' => true]);
        $this->assertInstanceOf(Unique::class, $converter);

        $converter = $this->buildConverter(['converter' => 'mock', 'unique' => false]);
        $this->assertInstanceOf(ConverterMock::class, $converter);
    }

    /**
     * Test the creation of a conditional converter.
     */
    public function testConditionConverter(): void
    {
        $converter = $this->buildConverter(['converter' => 'mock', 'condition' => 'true']);
        $this->assertInstanceOf(Conditional::class, $converter);
    }

    /**
     * Test the creation of a cache converter.
     */
    public function testCacheConverter(): void
    {
        $converter = $this->buildConverter(['converter' => 'mock', 'cache_key' => 'test']);
        $this->assertInstanceOf(Cache::class, $converter);
    }

    /**
     * Test the creation of nested converters.
     */
    public function testNestedConverters(): void
    {
        $converter = $this->buildConverter([
            'converter' => 'chain',
            'parameters' => [
                'converters' => [
                    ['converter' => 'mock'],
                    ['converter' => 'mock'],
                ],
            ],
        ]);
        $this->assertInstanceOf(Chain::class, $converter);

        // Conversion must have been done twice
        $value = $converter->convert('value');
        $this->assertSame('test_test_value', $value);
    }

    /**
     * Assert that an exception is thrown when the converter is set but empty.
     */
    public function testEmptyConverter(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->buildConverter([]);
    }

    /**
     * Assert that an exception is thrown when the converter name is not set.
     */
    public function testConverterNameNotSet(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->buildConverter([]);
    }

    /**
     * Assert that an exception is thrown when the converter is not defined.
     */
    public function testConverterNotDefined(): void
    {
        $this->expectException(RuntimeException::class);
        $this->buildConverter(['converter' => 'notExists']);
    }

    /**
     * Assert that an exception is thrown when the parameter "converter" is used,
     * but the value is not a converter definition.
     */
    public function testConverterParameterMalformed(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->buildConverter([
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
        $this->expectException(UnexpectedValueException::class);
        $this->buildConverter([
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
        $this->expectException(UnexpectedValueException::class);
        $this->buildConverter([
            'converter' => 'mock',
            'parameters' => [
                'converters' => [null],
            ],
        ]);
    }

    /**
     * Assert that an exception is thrown if the dump context was not set and a DumpContextAware converter is built.
     */
    public function testMissingDumpContext(): void
    {
        $this->expectException(RuntimeException::class);
        $this->buildConverter([
            'converter' => 'mock',
            'condition' => '{{id}} === 1',
        ], false);
    }

    /**
     * Build a converter with the specified data.
     */
    private function buildConverter(array $data, bool $withDumpContext = true): ConverterInterface
    {
        $builder = $this->createBuilder();
        if ($withDumpContext) {
            $builder->setDumpContext($this->getDumpContext());
        }

        return $builder->build(new ConverterConfig($data));
    }

    /**
     * Create a converter factory object.
     */
    private function createBuilder(): ConverterBuilder
    {
        $resolver = new ConverterAliasResolver();
        $container = new Container();
        $container->set($resolver->getAliasByName('cache'), new Cache());
        $container->set($resolver->getAliasByName('chain'), new Chain());
        $container->set($resolver->getAliasByName('conditional'), new Conditional(new ConditionBuilder()));
        $container->set($resolver->getAliasByName('faker'), new Faker(new FakerService()));
        $container->set($resolver->getAliasByName('mock'), new ConverterMock());
        $container->set($resolver->getAliasByName('unique'), new Unique());

        return new ConverterBuilder(new ConverterFactory($container, $resolver));
    }
}
