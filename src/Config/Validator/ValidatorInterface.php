<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Validator;

interface ValidatorInterface
{
    /**
     * Validate the data.
     *
     * @throws ValidationException
     */
    public function validate(mixed $data): ValidationResultInterface;
}
