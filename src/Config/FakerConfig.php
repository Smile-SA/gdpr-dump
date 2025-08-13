<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config;

use Smile\GdprDump\Config\Exception\MappingException;
use Smile\GdprDump\Config\Loader\Container;

final class FakerConfig extends Container
{
    public function getLocale(): string
    {
        return $this->get('locale', 'en_US');
    }

    public function setLocale(string $locale): self
    {
        return $this->set('locale', $locale);
    }

    public function fromArray(array $items): self
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
