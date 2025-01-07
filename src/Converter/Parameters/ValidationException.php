<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Parameters;

use Exception;
use Throwable;

final class ValidationException extends Exception
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
