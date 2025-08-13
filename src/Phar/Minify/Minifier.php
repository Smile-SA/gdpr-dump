<?php

declare(strict_types=1);

namespace Smile\GdprDump\Phar\Minify;

use Exception;

interface Minifier
{
    /**
     * Minify contents.
     *
     * @throws Exception
     */
    public function minify(string $contents): string;

    /**
     * Check if the minifier supports this file extension.
     */
    public function supports(string $extension): bool;
}
