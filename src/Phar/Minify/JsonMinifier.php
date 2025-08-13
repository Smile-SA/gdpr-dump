<?php

declare(strict_types=1);

namespace Smile\GdprDump\Phar\Minify;

use JsonException;
use Smile\GdprDump\Phar\Exception\MinifyException;

final class JsonMinifier implements Minifier
{
    public function minify(string $contents): string
    {
        $decoded = json_decode($contents);
        if (json_last_error() !== JSON_ERROR_NONE || $decoded === null) {
            return $contents;
        }

        try {
            $encoded = json_encode($decoded, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (JsonException $e) {
            throw new MinifyException($e->getMessage(), $e);
        }

        return $encoded;
    }

    public function supports(string $extension): bool
    {
        return $extension === 'json';
    }
}
