<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Config\Definition;

use Smile\GdprDump\Dumper\Config\Definition\FakerSettings;
use Smile\GdprDump\Tests\Unit\TestCase;

class FakerSettingsTest extends TestCase
{
    /**
     * Test the creation of a faker settings object.
     */
    public function testObjectCreation(): void
    {
        $settings = new FakerSettings('en_US');
        $this->assertSame('en_US', $settings->getLocale());
    }
}
