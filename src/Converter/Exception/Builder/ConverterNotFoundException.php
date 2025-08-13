<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Exception\Builder;

use Exception;
use Throwable;

/**
 * Exception thrown when a converter is not defined
 */
final class ConverterNotFoundException extends ConverterBuildException
{
}
