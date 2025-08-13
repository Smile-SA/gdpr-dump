<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Generator;

use Smile\GdprDump\Converter\Converter;

final class SetNull implements Converter
{
    public function setParameters(array $parameters): void
    {
        // No parameters
    }

    public function convert(mixed $value): mixed
    {
        return null;
    }
}
