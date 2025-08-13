<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Configuration\Definition;

use Smile\GdprDump\Configuration\Definition\ConverterConfig;
use Smile\GdprDump\Configuration\Exception\UnexpectedValueException;
use Smile\GdprDump\Tests\Unit\TestCase;

final class ConverterConfigTest extends TestCase
{
    /**
     * Test the creation of a converter config object.
     */
    public function testConverterConfig(): void
    {
        $name = 'randomizeText';
        $parameters = ['foo' => 'bar'];
        $condition = '1===1';
        $cacheKey = 'baz';
        $unique = true;

        $converterConfig = (new ConverterConfig($name))
            ->setParameters($parameters)
            ->setCondition($condition)
            ->setCacheKey($cacheKey)
            ->setUnique($unique);

        $this->assertSame($name, $converterConfig->getName());
        $this->assertSame($parameters, $converterConfig->getParameters());
        $this->assertSame($condition, $converterConfig->getCondition());
        $this->assertSame($cacheKey, $converterConfig->getCacheKey());
        $this->assertSame($unique, $converterConfig->isUnique());
    }

    /**
     * Test the default values.
     */
    public function testDefaultValues(): void
    {
        $converterConfig = new ConverterConfig('randomizeText');
        $this->assertSame([], $converterConfig->getParameters());
        $this->assertSame('', $converterConfig->getCondition());
        $this->assertSame('', $converterConfig->getCacheKey());
        $this->assertFalse($converterConfig->isUnique());
    }

    /**
     * Assert that an exception is thrown when the converter name is not specified or empty.
     */
    public function testEmptyConvertName(): void
    {
        $this->expectException(UnexpectedValueException::class);
        new ConverterConfig('');
    }

    /**
     * Assert that object properties are cloned.
     */
    public function testDeepClone(): void
    {
        $converterConfig = (new ConverterConfig('mock'))
            ->setParameters([
                'converter' => new ConverterConfig('mock'),
            ]);

        $clonedConfig = clone $converterConfig;
        $this->assertNotSame($converterConfig->getParameters(), $clonedConfig->getParameters());

        $converterConfig = (new ConverterConfig('mock'))
            ->setParameters([
                'converters' => [new ConverterConfig('mock')],
            ]);

        $clonedConfig = clone $converterConfig;
        $this->assertNotSame($converterConfig->getParameters(), $clonedConfig->getParameters());
    }
}
