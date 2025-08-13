<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Exception;

use Exception;
use Throwable;

/**
 * Exception thrown when invalid version data was provided (`version` and `if_version` parameters).
 */
final class InvalidVersionException extends Exception
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
