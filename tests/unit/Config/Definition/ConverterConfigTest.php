<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Definition;

use Smile\GdprDump\Configuration\Definition\ConverterConfig;
use Smile\GdprDump\Tests\Unit\TestCase;
use UnexpectedValueException;

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

        $configuration = (new ConverterConfig($name))
            ->setParameters($parameters)
            ->setCondition($condition)
            ->setCacheKey($cacheKey)
            ->setUnique($unique);

        $this->assertSame($name, $configuration->getName());
        $this->assertSame($parameters, $configuration->getParameters());
        $this->assertSame($condition, $configuration->getCondition());
        $this->assertSame($cacheKey, $configuration->getCacheKey());
        $this->assertSame($unique, $configuration->isUnique());
    }

    /**
     * Test the default values.
     */
    public function testDefaultValues(): void
    {
        $configuration = (new ConverterConfig('randomizeText'));
        $this->assertSame([], $configuration->getParameters());
        $this->assertSame('', $configuration->getCondition());
        $this->assertSame('', $configuration->getCacheKey());
        $this->assertFalse($configuration->isUnique());
    }

    /**
     * Assert that an exception is thrown when the converter name is not specified or empty.
     */
    public function testEmptyConvertName(): void
    {
        $this->expectException(UnexpectedValueException::class);
        new ConverterConfig('');
    }
}
