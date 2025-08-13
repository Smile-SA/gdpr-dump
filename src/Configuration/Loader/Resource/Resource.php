<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Loader\Resource;

final class Resource
{
    public function __construct(private string $input, private bool $isFile = true)
    {
    }

    public function getInput(): string
    {
        return $this->input;
    }

    public function isFile(): bool
    {
        return $this->isFile;
    }
}
