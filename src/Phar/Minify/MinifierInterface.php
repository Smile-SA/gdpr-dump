<?php

declare(strict_types=1);

namespace Smile\GdprDump\Phar\Minify;

interface MinifierInterface
{
    /**
     * Minify contents.
     *
     * @param string $contents
     * @return string
     */
    public function minify(string $contents): string;

    /**
     * Check if the minifier supports this file extension.
     *
     * @param string $extension
     * @return bool
     */
    public function supports(string $extension): bool;
}
