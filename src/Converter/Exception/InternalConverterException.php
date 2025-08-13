<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Exception;

/**
 * Exception thrown when an internal converter is used outside of the converter builder.
 */
final class InternalConverterException extends BuildException
{
}
