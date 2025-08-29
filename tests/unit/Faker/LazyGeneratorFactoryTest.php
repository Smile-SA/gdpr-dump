<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Faker;

use Faker\Factory;
use Faker\Provider;
use Smile\GdprDump\Faker\LazyGenerator;
use Smile\GdprDump\Faker\LazyGeneratorFactory;
use Smile\GdprDump\Tests\Unit\TestCase;

final class LazyGeneratorFactoryTest extends TestCase
{
    /**
     * Test the "create" method.
     */
    public function testFactory(): void
    {
        $factory = new LazyGeneratorFactory(Factory::DEFAULT_LOCALE);
        $lazyGenerator = $factory->create();
        $this->assertTrue($this->hasProvider(Provider\en_US\Address::class, $lazyGenerator));
    }

    /**
     * Test the "create" method with a custom locale.
     */
    public function testFactoryWithCustomLocale(): void
    {
        $factory = new LazyGeneratorFactory(Factory::DEFAULT_LOCALE);
        $lazyGenerator = $factory->create('fr_FR');
        $this->assertTrue($this->hasProvider(Provider\fr_FR\Address::class, $lazyGenerator));
    }

    /**
     * Check if the generator has the specified provider.
     */
    private function hasProvider(string $className, LazyGenerator $lazyGenerator): bool
    {
        $providers = $lazyGenerator->getGenerator()->getProviders();

        foreach ($providers as $provider) {
            if ($provider instanceof $className) {
                return true;
            }
        }

        return false;
    }
}
