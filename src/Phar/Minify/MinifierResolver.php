<?php

declare(strict_types=1);

namespace Smile\GdprDump\Phar\Minify;

// TODO unit tests
final class MinifierResolver
{
    /**
     * @param Minifier[] $minifiers
     */
    public function __construct(private iterable $minifiers)
    {
    }

    /**
     * Get a minifier by file extension.
     */
    public function getMinifier(string $extension): ?Minifier
    {
        foreach ($this->minifiers as $loader) {
            if ($loader->supports($extension)) {
                return $loader;
            }
        }

        return null;
    }
}
