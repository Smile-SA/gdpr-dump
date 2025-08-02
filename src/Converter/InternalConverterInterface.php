<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter;

/**
 * Converters that implement this interface cannot be used from the yaml config file.
 */
interface InternalConverterInterface extends ConverterInterface
{
}
