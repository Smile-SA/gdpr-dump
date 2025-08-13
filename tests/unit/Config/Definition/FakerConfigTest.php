<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Config\Definition;

use Smile\GdprDump\Config\Definition\FakerConfig;
use Smile\GdprDump\Tests\Unit\TestCase;

final class FakerConfigTest extends TestCase
{
    /**
     * Test the creation of a faker config object.
     */
    public function testObjectCreation(): void
    {
        $locale = 'fr_FR';
        $config = (new FakerConfig())->setLocale($locale);
        $this->assertSame($locale, $config->getLocale());
    }

    /**
     * Test the default values.
     */
    public function testDefaultValues(): void
    {
        $config = (new FakerConfig());
        $this->assertSame('en_US', $config->getLocale());
    }
}
