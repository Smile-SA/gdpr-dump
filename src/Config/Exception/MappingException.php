<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Exception;

use Exception;
use Throwable;

/**
 * Exception thrown when an object could not be mapped from the configuration data.
 */
class MappingException extends Exception
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
