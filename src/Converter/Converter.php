<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter;

use Smile\GdprDump\Converter\Exception\ConversionFailedException;
use Smile\GdprDump\Converter\Exception\InvalidParameterException;

interface Converter
{
    /**
     * Transform the value.
     *
     * @throws ConversionFailedException
     */
    public function convert(mixed $value): mixed;

    /**
     * Set the converter parameters.
     *
     * @throws InvalidParameterException
     */
    public function setParameters(array $parameters): void;
}
