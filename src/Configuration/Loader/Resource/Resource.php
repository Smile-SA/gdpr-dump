<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Loader\Resource;

final class Resource
{
    public function __construct(private string $input, private bool $isFile = true)
    {
    }

    /**
     * Get the input (filename if the resource is a file, or a yaml string otherwise).
     */
    public function getInput(): string
    {
        return $this->input;
    }

    /**
     * Returns true if the resource is a file.
     */
    public function isFile(): bool
    {
        return $this->isFile;
    }
}
