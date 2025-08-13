<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter;

/**
 * Converters that implement this interface cannot be used from the config file.
 */
interface IsInternal extends Converter
{
    public static function getAlternative(): string;
}
