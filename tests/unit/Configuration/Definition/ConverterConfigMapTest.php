<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Configuration\Definition;

use Smile\GdprDump\Configuration\Definition\ConverterConfig;
use Smile\GdprDump\Configuration\Definition\ConverterConfigMap;
use Smile\GdprDump\Tests\Unit\TestCase;

final class ConverterConfigMapTest extends TestCase
{
    /**
     * Assert that the map items are cloned.
     */
    public function testDeepClone(): void
    {
        $map = new ConverterConfigMap([
            'col1' => new ConverterConfig('randomizeText'),
            'col2' => new ConverterConfig('anonymizeText'),
        ]);

        $clonedMap = clone $map;
        $this->assertNotSame($map->toArray(), $clonedMap->toArray());
    }
}
