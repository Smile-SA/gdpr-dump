<?php

declare(strict_types=1);

namespace Smile\GdprDump\Util;

final class Arrays
{
    /**
     * Get an array value by path.
     */
    public static function getPath(array $array, string $path, mixed $default = null): mixed
    {
        $cur = $array;

        foreach (explode('.', $path) as $key) {
            if (!isset($cur[$key])) {
                $cur = $default;
                break;
            }

            $cur = $cur[$key];
        }

        return $cur;
    }

    /**
     * Set an array value by path.
     */
    public static function setPath(array &$array, string $path, mixed $value): void
    {
        $keys = explode('.', $path);
        $lastKey = array_pop($keys);
        $cur = &$array;

        foreach ($keys as $key) {
            if (!isset($cur[$key])) {
                $cur[$key] = [];
            }

            $cur = &$cur[$key];
        }

        $cur[$lastKey] = $value;
    }

    /**
     * Apply a callback on the specified array to rename or remove keys.
     */
    public static function mapKeys(array $input, callable $callback): array
    {
        foreach ($input as $key => $value) {
            $newKey = $callback($key, $value);

            if ($newKey === false) {
                unset($input[$key]);
            } elseif ($newKey !== $key) {
                $input[$newKey] = $value;
                unset($input[$key]);
            }
        }

        return $input;
    }
}
