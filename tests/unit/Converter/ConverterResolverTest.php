<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter;

use RuntimeException;
use Smile\GdprDump\Converter\ConverterResolver;
use Smile\GdprDump\Converter\Proxy\Faker;
use Smile\GdprDump\Converter\Proxy\Unique;
use Smile\GdprDump\Converter\Transformer\ToLower;
use Smile\GdprDump\Tests\Unit\TestCase;

class ConverterResolverTest extends TestCase
{
    /**
     * Test the converter resolver.
     */
    public function testResolver(): void
    {
        $resolver = $this->createResolver();

        // Converter names
        $this->assertSame(ToLower::class, $resolver->getClassName('toLower'));
        $this->assertSame(Faker::class, $resolver->getClassName('faker'));
        $this->assertSame(Unique::class, $resolver->getClassName('unique'));

        // Class names
        $this->assertSame(ToLower::class, $resolver->getClassName(ToLower::class));
        $this->assertSame(Faker::class, $resolver->getClassName(Faker::class));
        $this->assertSame(Unique::class, $resolver->getClassName(Unique::class));
    }

    /**
     * Assert that an exception is thrown when an empty string is passed to the resolver.
     */
    public function testEmptyParam(): void
    {
        $this->expectException(RuntimeException::class);
        $this->createResolver()->getClassName('');
    }

    /**
     * Assert that an exception is thrown when the converter is not defined.
     */
    public function testClassNotExists(): void
    {
        $this->expectException(RuntimeException::class);
        $this->createResolver()->getClassName('not_exists');
    }

    /**
     * Assert that an exception is thrown when the class exists but is not a converter.
     */
    public function testInvalidClassName(): void
    {
        $this->expectException(RuntimeException::class);
        $this->createResolver()->getClassName('converterFactory');
    }

    /**
     * Create a resolver object.
     */
    private function createResolver(): ConverterResolver
    {
        $resolver = new ConverterResolver();
        $resolver->addPath('Smile\\GdprDump\\Converter\\', dirname(__DIR__, 3) . '/src/Converter');

        return $resolver;
    }
}
