<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Config\Definition;

use Smile\GdprDump\Config\Definition\ConverterConfig;
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

        $config = (new ConverterConfig($name))
            ->setParameters($parameters)
            ->setCondition($condition)
            ->setCacheKey($cacheKey)
            ->setUnique($unique);

        $this->assertSame($name, $config->getName());
        $this->assertSame($parameters, $config->getParameters());
        $this->assertSame($condition, $config->getCondition());
        $this->assertSame($cacheKey, $config->getCacheKey());
        $this->assertSame($unique, $config->isUnique());
    }

    /**
     * Test the default values.
     */
    public function testDefaultValues(): void
    {
        $config = (new ConverterConfig('randomizeText'));
        $this->assertSame([], $config->getParameters());
        $this->assertSame('', $config->getCondition());
        $this->assertSame('', $config->getCacheKey());
        $this->assertFalse($config->isUnique());
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
