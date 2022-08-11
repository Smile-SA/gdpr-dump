<?php

declare(strict_types=1);

namespace Smile\GdprDump\Faker;

use Faker\Factory;
use Faker\Generator;

class FakerService
{
    private ?Generator $generator = null;

    public function __construct(private string $locale = Factory::DEFAULT_LOCALE)
    {
    }

    /**
     * Get the Faker generator.
     */
    public function getGenerator(): Generator
    {
        if ($this->generator === null) {
            $this->generator = Factory::create($this->locale);
        }

        return $this->generator;
    }

    /**
     * Get the current locale.
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Set the current locale.
     */
    public function setLocale(string $locale): self
    {
        if ($this->locale !== $locale) {
            $this->locale = $locale;
            $this->generator = null;
        }

        return $this;
    }
}
