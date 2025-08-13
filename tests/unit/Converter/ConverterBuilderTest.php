<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter;

use Smile\GdprDump\Config\Definition\ConverterConfig;
use Smile\GdprDump\Converter\ConditionBuilder;
use Smile\GdprDump\Converter\ConverterBuilder;
use Smile\GdprDump\Converter\ConverterFactory;
use Smile\GdprDump\Converter\Exception\ConverterBuildException;
use Smile\GdprDump\Converter\Proxy\Chain;
use Smile\GdprDump\Converter\Proxy\Faker;
use Smile\GdprDump\Converter\Proxy\Internal\Cache;
use Smile\GdprDump\Converter\Proxy\Internal\Conditional;
use Smile\GdprDump\Converter\Proxy\Internal\Unique;
use Smile\GdprDump\DependencyInjection\ConverterAliasResolver;
use Smile\GdprDump\Faker\FakerService;
use Smile\GdprDump\Tests\Framework\Mock\Converter\ConverterMock;
use Symfony\Component\DependencyInjection\Container;

final class ConverterBuilderTest extends TestCase implements DumpContextAware
{
    /**
     * Test the converter creation from an array definition.
     */
    public function testConverterCreation(): void
    {
        $config = (new ConverterConfig('mock'))->setParameters(['prefix' => '']);
        $converter = $this->createBuilder()->build($config);
        $this->assertInstanceOf(ConverterMock::class, $converter);
    }

    /**
     * Test the creation of a Faker converter.
     */
    public function testFakerConverter(): void
    {
        $config = (new ConverterConfig('faker'))
            ->setParameters(['formatter' => 'safeEmail']);

        $converter = $this->createBuilder()->build($config);
        $this->assertInstanceOf(Faker::class, $converter);
    }

    /**
     * Test the creation of a unique converter.
     */
    public function testUniqueConverter(): void
    {
        $config = (new ConverterConfig('mock'))
            ->setUnique(true);

        $converter = $this->createBuilder()->build($config);
        $this->assertInstanceOf(Unique::class, $converter);

        $config->setUnique(false);
        $converter = $this->createBuilder()->build($config);
        $this->assertInstanceOf(ConverterMock::class, $converter);
    }

    /**
     * Test the creation of a conditional converter.
     */
    public function testConditionConverter(): void
    {
        $config = (new ConverterConfig('mock'))
            ->setCondition('true');

        $converter = $this->createBuilder()
            ->setDumpContext($this->getDumpContext())
            ->build($config);
        $this->assertInstanceOf(Conditional::class, $converter);
    }

    /**
     * Test the creation of a cache converter.
     */
    public function testCacheConverter(): void
    {
        $config = (new ConverterConfig('mock'))
            ->setCacheKey('test');

        $converter = $this->createBuilder()->build($config);
        $this->assertInstanceOf(Cache::class, $converter);
    }

    /**
     * Test the creation of nested converters.
     */
    public function testNestedConverters(): void
    {
        $config = (new ConverterConfig('chain'))
            ->setParameters(['converters' => [['converter' => 'mock'], ['converter' => 'mock']]]);

        $converter = $this->createBuilder()->build($config);
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
        $config = (new ConverterConfig('notExists'));

        $this->expectException(ConverterBuildException::class);
        $this->createBuilder()->build($config);
    }

    /**
     * Assert that an exception is thrown when the parameter "converter" is used,
     * but the value is not a converter definition.
     */
    public function testConverterParameterMalformed(): void
    {
        $config = (new ConverterConfig('mock'))
            ->setParameters(['converter' => null]);

        $this->expectException(ConverterBuildException::class);
        $this->createBuilder()->build($config);
    }

    /**
     * Assert that an exception is thrown when the parameter "converters" is used,
     * but the value is not an array of converter definition.
     */
    public function testConvertersParameterMalformed(): void
    {
        $config = (new ConverterConfig('mock'))
            ->setParameters(['converters' => null]);

        $this->expectException(ConverterBuildException::class);
        $converter = $this->createBuilder()->build($config);
    }

    /**
     * Assert that an exception is thrown if the dump context was not set and a DumpContextAware converter is built.
     */
    public function testMissingDumpContext(): void
    {
        $config = (new ConverterConfig('mock'))
            ->setCondition('{{id}} === 1');

        $this->expectException(ConverterBuildException::class);
        $this->createBuilder()->build($config);
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
