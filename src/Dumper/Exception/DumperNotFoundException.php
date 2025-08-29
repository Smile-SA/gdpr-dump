<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Exception;

use Exception;
use Throwable;

/**
 * Exception thrown when providing an invalid dumper type to the factory.
 */
final class DumperNotFoundException extends Exception
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
