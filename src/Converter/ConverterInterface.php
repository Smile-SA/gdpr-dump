<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter;

interface ConverterInterface
{
    /**
     * Transform the value.
     */
    public function convert(mixed $value, array $context = []): mixed;

    /**
     * Set the converter parameters.
     */
    public function setParameters(array $parameters): void;
}
