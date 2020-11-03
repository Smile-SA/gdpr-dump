<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Parameters;

use Exception;
use Throwable;

class ValidationException extends Exception
{
    /**
     * @param string $message
     * @param Throwable|null $previous
     */
    public function __construct(string $message, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
