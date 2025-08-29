<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Configuration\Definition;

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

        $propagationConfig = (new FilterPropagationConfig())
            ->setEnabled(false)
            ->setIgnoredForeignKeys(['fk1']);

        $this->assertSame($enabled, $propagationConfig->isEnabled());
        $this->assertSame($ignoredForeignKeys, $propagationConfig->getIgnoredForeignKeys());
    }

    /**
     * Test the default values.
     */
    public function testDefaultValues(): void
    {
        $propagationConfig = new FilterPropagationConfig();
        $this->assertTrue($propagationConfig->isEnabled());
        $this->assertSame([], $propagationConfig->getIgnoredForeignKeys());
    }
}
