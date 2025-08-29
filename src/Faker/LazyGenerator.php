<?php

declare(strict_types=1);

namespace Smile\GdprDump\Faker;

use Faker\Factory;
use Faker\Generator;

final class LazyGenerator
{
    private Generator $generator;

    /**
     * @param non-empty-string $locale
     */
    public function __construct(private string $locale)
    {
    }

    /**
     * Get the Faker generator.
     */
    public function getGenerator(): Generator
    {
        if (!isset($this->generator)) {
            $this->generator = Factory::create($this->locale);
        }

        return $this->generator;
    }
}
