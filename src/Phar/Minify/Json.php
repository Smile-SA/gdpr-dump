<?php

declare(strict_types=1);

namespace Smile\GdprDump\Phar\Minify;

class Json implements MinifierInterface
{
    /**
     * @inheritdoc
     */
    public function minify(string $contents): string
    {
        $decoded = json_decode($contents);
        if (json_last_error() !== JSON_ERROR_NONE || $decoded === null) {
            return $contents;
        }

        return json_encode($decoded, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @inheritdoc
     */
    public function supports(string $extension): bool
    {
        return $extension === 'json';
    }
}
