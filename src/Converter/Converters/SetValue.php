<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Converters;

use Smile\GdprDump\Converter\Converter;
use Smile\GdprDump\Converter\Exception\InvalidParameterException;
use Smile\GdprDump\Converter\IsConfigurable;

final class SetValue implements Converter, IsConfigurable
{
    private mixed $value;

    public function setParameters(array $parameters): void
    {
        // The parameter must be specified, but accepts empty values
        if (!array_key_exists('value', $parameters)) {
            throw new InvalidParameterException('The parameter "value" is required.');
        }

        if ($parameters['value'] !== null && !is_scalar($parameters['value'])) {
            throw new InvalidParameterException('The parameter "value" must be a scalar or null.');
        }

        $this->value = $parameters['value'];
    }

    public function convert(mixed $value): mixed
    {
        return $this->value;
    }
}
