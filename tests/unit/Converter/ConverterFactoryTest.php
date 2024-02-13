<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter;

use RuntimeException;
use Smile\GdprDump\Converter\ConverterFactory;
use Smile\GdprDump\DependencyInjection\Compiler\ConverterAliasPass;
use Smile\GdprDump\Tests\Framework\Mock\Converter\ConverterMock;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class ConverterFactoryTest extends TestCase
{
    /**
     * Test the converter factory.
     */
    public function testConverterCreation(): void
    {
        $converter = $this->createFactory()
            ->create('test');

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
        $containerMock = $this->createMock(Container::class);
        $containerMock
            ->method('get')
            ->will(
                $this->returnCallback(
                    fn (string $value) => match ($value) {
                        ConverterAliasPass::ALIAS_PREFIX . 'test' => new ConverterMock(),
                        default => throw new ServiceNotFoundException(ConverterAliasPass::ALIAS_PREFIX . $value),
                    }
                )
            );

        return new ConverterFactory($containerMock);
    }
}
