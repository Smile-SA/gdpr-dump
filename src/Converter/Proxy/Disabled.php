<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Proxy;

use Smile\GdprDump\Converter\Converter;

// TODO rename
final class Disabled implements Converter
{
    public function setParameters(array $parameters): void
    {
    }

    public function convert(mixed $value): mixed
    {
        return $value;
    }
}
