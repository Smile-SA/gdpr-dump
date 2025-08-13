<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Resource;

final class Resource
{
    public function __construct(private string $input, private string $type, private bool $isFile = true)
    {
    }

    public function getInput(): string
    {
        return $this->input;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isFile(): bool
    {
        return $this->isFile;
    }
}
