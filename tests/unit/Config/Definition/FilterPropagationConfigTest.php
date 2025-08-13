<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Config\Definition;

use Smile\GdprDump\Config\Definition\FilterPropagationConfig;
use Smile\GdprDump\Tests\Unit\TestCase;

final class FilterPropagationConfigTest extends TestCase
{
    /**
     * Test the creation of a filter propagation config object.
     */
    public function testObjectCreation(): void
    {
        $enabled = false;
        $ignoredForeignKeys = ['fk1'];

        $config = (new FilterPropagationConfig())
            ->setEnabled(false)
            ->setIgnoredForeignKeys(['fk1']);

        $this->assertSame($enabled, $config->isEnabled());
        $this->assertSame($ignoredForeignKeys, $config->getIgnoredForeignKeys());
    }

    /**
     * Test the default values.
     */
    public function testDefaultValues(): void
    {
        $config = (new FilterPropagationConfig());
        $this->assertTrue($config->isEnabled());
        $this->assertSame([], $config->getIgnoredForeignKeys());
    }
}
