<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Config\Resolver;

interface PathResolverInterface
{
    /**
     * Resolve a path.
     *
     * @param string $path
     * @return string
     */
    public function resolve(string $path): string;
}
