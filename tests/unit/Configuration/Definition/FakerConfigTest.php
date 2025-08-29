<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Configuration\Definition;

use Smile\GdprDump\Configuration\Definition\FakerConfig;
use Smile\GdprDump\Tests\Unit\TestCase;

final class FakerConfigTest extends TestCase
{
    /**
     * Test the creation of a faker config object.
     */
    public function testObjectCreation(): void
    {
        $locale = 'fr_FR';
        $fakerConfig = (new FakerConfig())->setLocale($locale);
        $this->assertSame($locale, $fakerConfig->getLocale());
    }

    /**
     * Test the default values.
     */
    public function testDefaultValues(): void
    {
        $fakerConfig = new FakerConfig();
        $this->assertSame('en_US', $fakerConfig->getLocale());
    }
}
