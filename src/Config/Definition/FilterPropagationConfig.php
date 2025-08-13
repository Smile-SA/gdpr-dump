<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Definition;

use Smile\GdprDump\Config\Exception\MappingException;

final class FilterPropagationConfig
{
    private bool $enabled = true;

    /**
     * @var string[]
     */
    private array $ignoredForeignKeys = [];

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getIgnoredForeignKeys(): array
    {
        return $this->ignoredForeignKeys;
    }

    /**
     * @param string[] $foreignKeys
     */
    public function setIgnoredForeignKeys(array $foreignKeys): self
    {
        $this->ignoredForeignKeys = $foreignKeys;

        return $this;
    }

    public function fromArray(array $items): static
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
