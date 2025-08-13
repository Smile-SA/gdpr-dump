<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Parser\Enum;

enum Formats: string implements Format
{
    case JSON = 'json';
    case YAML_FILE = 'yaml_file';

    public function getName(): string
    {
        return $this->value;
    }

    public function isFile(): bool
    {
        return $this === Formats::YAML_FILE;
    }
}
