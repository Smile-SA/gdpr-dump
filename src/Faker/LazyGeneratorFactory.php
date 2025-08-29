<?php

declare(strict_types=1);

namespace Smile\GdprDump\Faker;

final class LazyGeneratorFactory
{
    /**
     * @param non-empty-string $defaultLocale
     */
    public function __construct(private string $defaultLocale)
    {
    }

    /**
     * Get a lazy Faker generator.
     */
    public function create(string $locale = ''): LazyGenerator
    {
        if ($locale === '') {
            $locale = $this->defaultLocale;
        }

        return new LazyGenerator($locale);
    }
}
