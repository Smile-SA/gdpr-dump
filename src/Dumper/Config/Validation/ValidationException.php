<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Config\Validation;

use Exception;
use Throwable;

class ValidationException extends Exception
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
