<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Definition;

final class FakerConfig
{
    private string $locale = 'en_US';

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }
}
