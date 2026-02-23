<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter;

interface ConverterInterface
{
    /**
     * Transform the value.
     *
     * @param array<string, mixed> $context
     */
    public function convert(mixed $value, array $context = []): mixed;

    /**
     * Set the converter parameters.
     *
     * @param array<string, mixed> $parameters
     */
    public function setParameters(array $parameters): void;
}
