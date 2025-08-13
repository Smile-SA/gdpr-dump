<?php

declare(strict_types=1);

namespace Smile\GdprDump\Util;

final class Platform
{
    /**
     * Returns whether the file path is an absolute path.
     */
    public static function isAbsolutePath(string $path): bool
    {
        if ($path === '') {
            return false;
        }

        // Unix filesystem or network path
        if ($path[0] === '/' || $path[0] === '\\') {
            return true;
        }

        // Windows filesystem
        return strlen($path) >= 3
            && ctype_alpha($path[0])
            && $path[1] === ':'
            && ($path[2] === '\\' || $path[2] === '/');
    }
}
