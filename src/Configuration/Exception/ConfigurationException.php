<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Exception;

use Exception;
use Throwable;

/**
 * Exception thrown when an error related to the configuration occurred.
 */
abstract class ConfigurationException extends Exception
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
