<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config;

use Exception;
use Throwable;

class ConfigException extends Exception
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
