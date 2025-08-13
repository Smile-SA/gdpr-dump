<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Validator;

use Smile\GdprDump\Config\Exception\InvalidJsonSchemaException;

interface SchemaValidator
{
    /**
     * Validate an array or object that represents the configuration.
     *
     * @throws InvalidJsonSchemaException
     */
    public function validate(array|object $dataObject): ValidationResult;
}
