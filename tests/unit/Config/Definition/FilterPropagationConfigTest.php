<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Definition;

use Smile\GdprDump\Configuration\Definition\FilterPropagationConfig;
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

        $configuration = (new FilterPropagationConfig())
            ->setEnabled(false)
            ->setIgnoredForeignKeys(['fk1']);

        $this->assertSame($enabled, $configuration->isEnabled());
        $this->assertSame($ignoredForeignKeys, $configuration->getIgnoredForeignKeys());
    }

    /**
     * Test the default values.
     */
    public function testDefaultValues(): void
    {
        $configuration = (new FilterPropagationConfig());
        $this->assertTrue($configuration->isEnabled());
        $this->assertSame([], $configuration->getIgnoredForeignKeys());
    }
}
