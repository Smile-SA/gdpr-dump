<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Parser\Enum;

interface Format
{
    /**
     * Get the resource name.
     */
    public function getName(): string;

    /**
     * Check whether the resource is a file.
     */
    public function isFile(): bool;
}
