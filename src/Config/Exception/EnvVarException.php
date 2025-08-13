<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Exception;

use Exception;
use Throwable;

/**
 * Exception thrown when an env var could not be parsed.
 */
final class EnvVarException extends ConfigLoadException
{
}
