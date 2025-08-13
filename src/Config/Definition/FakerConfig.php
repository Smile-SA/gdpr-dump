<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Definition;

use Smile\GdprDump\Config\Exception\MappingException;

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

    public function fromArray(array $items): static
    {
        foreach ($items as $property => $value) {
            $items[$property] = match ($property) {
                'locale' => $this->setLocale($value),
                default => throw new MappingException(sprintf('Unsupported converter property "%s".', $property)),
            };
        }

        return $this;
    }
}
