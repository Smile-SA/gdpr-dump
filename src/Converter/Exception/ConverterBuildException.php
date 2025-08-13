<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Exception;

use Exception;
use Throwable;

/**
 * Exception thrown when an error occurred while building a converter.
 */
class ConverterBuildException extends Exception
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
