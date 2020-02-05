<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Validator;

interface ValidatorInterface
{
    /**
     * Validate the data.
     *
     * @param mixed $data
     * @return ValidationResultInterface
     * @throws ValidationException
     */
    public function validate($data): ValidationResultInterface;
}
