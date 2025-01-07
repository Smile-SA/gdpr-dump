<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter;

use RuntimeException;
use Smile\GdprDump\Converter\ConverterFactory;
use Smile\GdprDump\DependencyInjection\ConverterAliasResolver;
use Smile\GdprDump\Tests\Framework\Mock\Converter\ConverterMock;
use Symfony\Component\DependencyInjection\Container;

final class ConverterFactoryTest extends TestCase
{
    /**
     * Test the converter factory.
     */
    public function testConverterCreation(): void
    {
        $converter = $this->createFactory()
            ->create('mock');

        $this->assertInstanceOf(ConverterMock::class, $converter);
    }

    /**
     * Assert that an exception is thrown when the converter is not defined.
     */
    public function testConverterNotDefined(): void
    {
        $this->expectException(RuntimeException::class);
        $this->createFactory()
            ->create('notExists');
    }

    /**
     * Create a converter factory object.
     */
    private function createFactory(): ConverterFactory
    {
        $resolver = new ConverterAliasResolver();
        $container = new Container();
        $container->set($resolver->getAliasByName('mock'), new ConverterMock());

        return new ConverterFactory($container, $resolver);
    }
}
