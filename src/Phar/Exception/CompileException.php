<?php

declare(strict_types=1);

namespace Smile\GdprDump\Phar\Exception;

use Exception;
use Throwable;

/**
 * Exception thrown when the compilation failed.
 */
class CompileException extends Exception
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
