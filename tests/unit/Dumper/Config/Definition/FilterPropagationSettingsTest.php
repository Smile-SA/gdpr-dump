<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Config\Definition;

use Smile\GdprDump\Dumper\Config\Definition\FilterPropagationSettings;
use Smile\GdprDump\Tests\Unit\TestCase;

class FilterPropagationSettingsTest extends TestCase
{
    /**
     * Test the creation of a filter propagation settings object.
     */
    public function testObjectCreation(): void
    {
        $settings = new FilterPropagationSettings(true, ['fk1']);
        $this->assertTrue($settings->isEnabled());
        $this->assertSame(['fk1'], $settings->getIgnoredForeignKeys());

        $settings = new FilterPropagationSettings(false, []);
        $this->assertFalse($settings->isEnabled());
        $this->assertSame([], $settings->getIgnoredForeignKeys());
    }
}
