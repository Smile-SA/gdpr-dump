<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter;

use Smile\GdprDump\Converter\Exception\ConversionException;

interface Converter
{
    /**
     * Transform the value.
     *
     * @throws ConversionException
     */
    public function convert(mixed $value): mixed;
}
