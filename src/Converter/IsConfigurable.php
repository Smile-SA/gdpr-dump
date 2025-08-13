<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter;

use Smile\GdprDump\Converter\Exception\InvalidParameterException;

interface IsConfigurable
{
    /**
     * Set the converter parameters.
     *
     * @throws InvalidParameterException
     */
    public function setParameters(array $parameters): void;
}
