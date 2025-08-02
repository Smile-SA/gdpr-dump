<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Generator;

use Smile\GdprDump\Converter\ConverterInterface;

final class SetNull implements ConverterInterface
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
