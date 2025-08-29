<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Converters;

use Smile\GdprDump\Converter\Converter;

final class SetNull implements Converter
{
    public function convert(mixed $value): mixed
    {
        return null;
    }
}
