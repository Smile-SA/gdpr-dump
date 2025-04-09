<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter;

use Smile\GdprDump\Converter\Parameters\ValidationException;

interface ConverterInterface
{
    /**
     * Transform the value.
     */
    public function convert(mixed $value, array $context = []): mixed;

    /**
     * Set the converter parameters.
     *
     * @throws ValidationException
     */
    public function setParameters(array $parameters): void;
}
