<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Exception;

use Exception;
use Throwable;

/**
 * Exception thrown when a converter is not defined
 */
final class ConverterNotFoundException extends Exception
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
