<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Config\Definition;

use Smile\GdprDump\Dumper\Config\Definition\ConverterConfig;
use Smile\GdprDump\Tests\Unit\TestCase;
use UnexpectedValueException;

final class ConverterConfigTest extends TestCase
{
    /**
     * Test the creation of a converter config.
     */
    public function testConverterConfig(): void
    {
        $data = [
            'converter' => 'converter1',
            'condition' => 'true',
            'cache_key' => 'cache1',
            'unique' => true,
            'parameters' => ['value' => 'val1'],
        ];

        $config = new ConverterConfig($data);
        $this->assertSame($data['converter'], $config->getName());
        $this->assertSame($data['condition'], $config->getCondition());
        $this->assertSame($data['cache_key'], $config->getCacheKey());
        $this->assertSame($data['unique'], $config->isUnique());
        $this->assertSame($data['parameters'], $config->getParameters());
    }

    /**
     * Test the creation of a converter config with empty data.
     */
    public function testEmptyData(): void
    {
        $config = new ConverterConfig(['converter' => 'converter1']);

        $this->assertSame('converter1', $config->getName());
        $this->assertSame('', $config->getCondition());
        $this->assertSame('', $config->getCacheKey());
        $this->assertFalse($config->isUnique());
        $this->assertSame([], $config->getParameters());
    }

    /**
     * Assert that an exception is thrown when the converter name is not specified or empty.
     */
    public function testEmptyConvertName(): void
    {
        $this->expectException(UnexpectedValueException::class);
        new ConverterConfig(['converter' => '']);
    }
}
