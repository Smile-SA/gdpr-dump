<?php

declare(strict_types=1);

namespace Smile\GdprDump\Phar\Minify;

interface MinifierInterface
{
    /**
     * Minify contents.
     */
    public function minify(string $contents): string;

    /**
     * Check if the minifier supports this file extension.
     */
    public function supports(string $extension): bool;
}
