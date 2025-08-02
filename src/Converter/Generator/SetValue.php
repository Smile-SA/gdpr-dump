<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Generator;

use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\ValidationException;

final class SetValue implements ConverterInterface
{
    private mixed $value;

    public function setParameters(array $parameters): void
    {
        // The parameter must be specified, but accepts empty values
        if (!array_key_exists('value', $parameters)) {
            throw new ValidationException('The parameter "value" is required.');
        }

        if ($parameters['value'] !== null && !is_scalar($parameters['value'])) {
            throw new ValidationException('The parameter "value" must be a scalar or null.');
        }

        $this->value = $parameters['value'];
    }

    public function convert(mixed $value): mixed
    {
        return $this->value;
    }
}
