<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Config\Definition;

class FakerSettings
{
    public function __construct(private string $locale)
    {
    }

    /**
     * Get the faker locale.
     */
    public function getLocale(): string
    {
        return $this->locale;
    }
}
