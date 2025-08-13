<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Exception\Builder;

/**
 * Exception thrown when a converter does not have access to the dump context.
 */
final class MissingContextException extends ConverterBuildException
{
}
