<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Exception;

use Exception;
use Throwable;

/**
 * Exception thrown when an error occurred during the dump.
 */
final class DumpException extends Exception
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
