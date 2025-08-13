<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Exception;

use Exception;
use Throwable;

/**
 * Exception thrown when the parameters of a converter are invalid.
 */
final class InvalidParameterException extends ConverterBuildException
{
}
