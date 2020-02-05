<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Resolver;

interface PathResolverInterface
{
    /**
     * Resolve a path (either relative or absolute).
     *
     * @param string $path
     * @param string|null $currentPath
     * @return string
     * @throws FileNotFoundException
     */
    public function resolve(string $path, string $currentPath = null): string;
}
