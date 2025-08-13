<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config;

final class FilterPropagationConfigOld
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
}
