<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Converters;

use Closure;
use Smile\GdprDump\Converter\Converter;

final class RandomizeNumber implements Converter
{
    private Closure $replaceCallback;

    public function __construct()
    {
        $this->replaceCallback = fn (): string => (string) mt_rand(0, 9);
    }

    public function convert(mixed $value): string
    {
        $value = (string) $value;

        return $value !== ''
            ? (string) preg_replace_callback('/[0-9]/', $this->replaceCallback, $value)
            : $value;
    }
}
