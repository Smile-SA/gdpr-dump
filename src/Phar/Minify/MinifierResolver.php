<?php

declare(strict_types=1);

namespace Smile\GdprDump\Phar\Minify;

use Psr\Container\ContainerInterface;

final class MinifierResolver
{
    public function __construct(private ContainerInterface $minifierLocator)
    {
    }

    /**
     * Get a minifier by file extension.
     */
    public function getMinifier(string $extension): ?Minifier
    {
        return $this->minifierLocator->has($extension)
            ? $this->minifierLocator->get($extension)
            : null;
    }
}
