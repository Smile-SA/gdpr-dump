<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Resource;

final class FileResource implements Resource
{
    private string $extension;

    public function __construct(private string $fileName)
    {
    }

    public function getInput(): string
    {
        return $this->fileName;
    }

    public function __toString(): string
    {
        return $this->fileName;
    }

    /**
     * Get the file extension.
     */
    public function getExtension(): string
    {
        if (!isset($this->extension)) {
            $this->extension = pathinfo($this->getInput(), PATHINFO_EXTENSION);
        }

        return $this->extension;
    }
}
