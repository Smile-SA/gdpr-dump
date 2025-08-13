<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Resource;

final class JsonResource implements Resource
{
    public function __construct(private string $input)
    {
    }

    public function getInput(): string
    {
        return $this->input;
    }

    public function __toString(): string
    {
        // Don't return the input to prevent sensitive data from being outputted
        return 'JsonResource';
    }
}
