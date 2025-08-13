<?php

declare(strict_types=1);

namespace Smile\GdprDump\Phar\Minify;

use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('json')]
final class JsonMinifier implements Minifier
{
    public function minify(string $contents): string
    {
        $decoded = json_decode($contents);
        if (json_last_error() !== JSON_ERROR_NONE || $decoded === null) {
            return $contents;
        }

        return json_encode($decoded, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function supports(string $extension): bool
    {
        return $extension === 'json';
    }
}
