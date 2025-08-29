<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter;

/**
 * Converters that implement this interface cannot be used from the config file.
 */
interface IsInternal
{
    /**
     * Get the error to display when trying to use an internal converter.
     */
    public static function getAlternative(): string;
}
