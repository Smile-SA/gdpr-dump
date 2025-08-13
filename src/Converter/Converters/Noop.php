<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Converters;

use Smile\GdprDump\Converter\Converter;

/**
 * Converter that doesn't do anything.
 */
final class Noop implements Converter
{
    public function convert(mixed $value): mixed
    {
        return $value;
    }
}
