<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter;

use Faker\Factory;
use Smile\GdprDump\Configuration\Definition\ConverterConfig;
use Smile\GdprDump\Converter\ConverterBuilder;
use Smile\GdprDump\Converter\ConverterFactory;
use Smile\GdprDump\Converter\Converters\Chain;
use Smile\GdprDump\Converter\Converters\Faker;
use Smile\GdprDump\Converter\Converters\Internal\Cache;
use Smile\GdprDump\Converter\Converters\Internal\Conditional;
use Smile\GdprDump\Converter\Converters\Internal\Unique;
use Smile\GdprDump\Converter\Exception\ConverterNotFoundException;
use Smile\GdprDump\Converter\Exception\InvalidParameterException;
use Smile\GdprDump\DependencyInjection\ConverterAliasResolver;
use Smile\GdprDump\Faker\LazyGenerator;
use Smile\GdprDump\Tests\Framework\Mock\Converter\ConverterMock;
use Symfony\Component\DependencyInjection\Container;

final class ConverterBuilderTest extends TestCase implements DumpContextAware
{
    /**
     * Test the converter creation from an array definition.
     */
    public function testConverterCreation(): void
    {
        $configuration = (new ConverterConfig('mock'))->setParameters(['prefix' => '']);
        $converter = $this->createBuilder()->build($configuration);
        $this->assertInstanceOf(ConverterMock::class, $converter);
    }

    /**
     * Test the creation of a Faker converter.
     */
    public function testFakerConverter(): void
    {
        $configuration = (new ConverterConfig('faker'))
            ->setParameters(['formatter' => 'safeEmail']);

        $converter = $this->createBuilder()->build($configuration);
        $this->assertInstanceOf(Faker::class, $converter);
    }

    /**
     * Test the creation of a unique converter.
     */
    public function testUniqueConverter(): void
    {
        $configuration = (new ConverterConfig('mock'))
            ->setUnique(true);

        $converter = $this->createBuilder()->build($configuration);
        $this->assertInstanceOf(Unique::class, $converter);

        $configuration->setUnique(false);
        $converter = $this->createBuilder()->build($configuration);
        $this->assertInstanceOf(ConverterMock::class, $converter);
    }

    /**
     * Test the creation of a conditional converter.
     */
    public function testConditionConverter(): void
    {
        $configuration = (new ConverterConfig('mock'))
            ->setCondition('true');

        $converter = $this->createBuilder()
            ->build($configuration);
        $this->assertInstanceOf(Conditional::class, $converter);
    }

    /**
     * Test the creation of a cache converter.
     */
    public function testCacheConverter(): void
    {
        $configuration = (new ConverterConfig('mock'))
            ->setCacheKey('test');

        $converter = $this->createBuilder()->build($configuration);
        $this->assertInstanceOf(Cache::class, $converter);
    }

    /**
     * Test the creation of nested converters.
     */
    public function testNestedConverters(): void
    {
        $configuration = (new ConverterConfig('chain'))
            ->setParameters(['converters' => [new ConverterConfig('mock'), new ConverterConfig('mock')]]);

        $converter = $this->createBuilder()->build($configuration);
        $this->assertInstanceOf(Chain::class, $converter);

        // Conversion must have been done twice
        $value = $converter->convert('value');
        $this->assertSame('test_test_value', $value);
    }

    /**
     * Assert that an exception is thrown when the converter is not defined.
     */
    public function testConverterNotDefined(): void
    {
        $configuration = new ConverterConfig('notExists');

        $this->expectException(ConverterNotFoundException::class);
        $this->createBuilder()->build($configuration);
    }

    /**
     * Assert that an exception is thrown when the parameter "converter" is used,
     * but the value is not a converter definition.
     */
    public function testConverterParameterMalformed(): void
    {
        $configuration = (new ConverterConfig('mock'))
            ->setParameters(['converter' => ['converter' => 'mock']]);

        $this->expectException(InvalidParameterException::class);
        $this->createBuilder()->build($configuration);
    }

    /**
     * Assert that an exception is thrown when the parameter "converters" is used,
     * but the value is not an array of converter definition.
     */
    public function testConvertersParameterMalformed(): void
    {
        $configuration = (new ConverterConfig('mock'))
            ->setParameters(['converters' => ['converter' => 'mock']]);

        $this->expectException(InvalidParameterException::class);
        $this->createBuilder()->build($configuration);
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
        $container->set($resolver->getAliasByName('conditional'), new Conditional());
        $container->set($resolver->getAliasByName('faker'), new Faker());
        $container->set($resolver->getAliasByName('mock'), new ConverterMock());
        $container->set($resolver->getAliasByName('unique'), new Unique());

        return new ConverterBuilder(
            new ConverterFactory($container, $resolver),
            $this->getDumpContext(),
            new LazyGenerator(Factory::DEFAULT_LOCALE)
        );
    }
}
