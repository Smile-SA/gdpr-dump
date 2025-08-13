<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Faker;

use Smile\GdprDump\Faker\LazyGenerator;
use Smile\GdprDump\Tests\Unit\TestCase;

final class LazyGeneratorTest extends TestCase
{
    /**
     * Test the lazy generator.
     */
    public function testLazyGenerator(): void
    {
        $lazyUs1 = new LazyGenerator('en_US');
        $lazyUs2 = new LazyGenerator('en_US');
        $lazyFr = new LazyGenerator('fr_FR');

        $this->assertEquals($lazyUs1->getGenerator()->getProviders(), $lazyUs2->getGenerator()->getProviders());
        $this->assertNotEquals($lazyUs1->getGenerator()->getProviders(), $lazyFr->getGenerator()->getProviders());

        // Make sure the generator is working properly
        $this->assertSame(1, $lazyUs1->getGenerator()->numberBetween(1, 1));
    }
}
