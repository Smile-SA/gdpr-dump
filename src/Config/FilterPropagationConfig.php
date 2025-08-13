<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config;

use Smile\GdprDump\Config\Exception\MappingException;
use Smile\GdprDump\Config\Loader\Container;

final class FilterPropagationConfig extends Container
{
    public function isEnabled(): bool
    {
        return $this->get('enabled', true);
    }

    public function setEnabled(bool $enabled): self
    {
        return $this->set('enabled', $enabled);
    }

    /**
     * @return string[]
     */
    public function getIgnoredForeignKeys(): array
    {
        return $this->get('ignored_foreign_keys', []);
    }

    /**
     * @param string[] $foreignKeys
     */
    public function setIgnoredForeignKeys(array $foreignKeys): self
    {
        return $this->set('ignored_foreign_keys', $foreignKeys);
    }

    public function fromArray(array $items): self
    {
        foreach ($items as $property => $value) {
            $items[$property] = match ($property) {
                'enabled' => $this->setEnabled($value),
                'ignored_foreign_keys' => $this->setIgnoredForeignKeys($value),
                default => throw new MappingException(sprintf('Unsupported propagation property "%s".', $property)),
            };
        }

        return $this;
    }
}
