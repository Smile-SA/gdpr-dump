<?php

declare(strict_types=1);

namespace Smile\GdprDump\Faker;

use Faker\Factory;
use Faker\Generator;

class FakerService
{
    /**
     * @var Generator|null
     */
    private ?Generator $generator = null;

    /**
     * @var string
     */
    private string $locale;

    /**
     * @param string $locale
     */
    public function __construct(string $locale = Factory::DEFAULT_LOCALE)
    {
        $this->locale = $locale;
    }

    /**
     * Get the Faker generator.
     *
     * @return Generator
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
     *
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Set the current locale.
     *
     * @param string $locale
     * @return $this
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
