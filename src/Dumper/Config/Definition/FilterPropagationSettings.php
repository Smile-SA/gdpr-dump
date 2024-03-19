<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Config\Definition;

class FilterPropagationSettings
{
    public function __construct(private bool $enabled, private array $ignoredForeignKeys)
    {
    }

    /**
     * Checker whether filter propagation is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Get foreign keys to ignore when propagating filters to table dependencies.
     */
    public function getIgnoredForeignKeys(): array
    {
        return $this->ignoredForeignKeys;
    }
}
