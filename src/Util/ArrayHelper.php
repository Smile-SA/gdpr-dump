<?php

declare(strict_types=1);

namespace Smile\GdprDump\Util;

use RuntimeException;

final class ArrayHelper
{
    /**
     * Get an array value by path.
     */
    public function getPath(array $array, string $path, mixed $default = null): mixed
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
    public function setPath(array &$array, string $path, mixed $value): void
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
     * Apply the mapping to the specified array.
     *
     * @param array<string, mixed> $input
     * @param array<string, string> $mapping
     * @return array<string, mixed>
     * @throws RuntimeException
     */
    public function map(array $input, array $mapping): array
    {
        foreach ($input as $key => $value) {
            if (!array_key_exists($key, $mapping)) {
                throw new RuntimeException(sprintf('The property "%s" is not supported.', $key));
            }

            if ($mapping[$key] !== $key) {
                $input[$mapping[$key]] = $value;
                unset($input[$key]);
            }
        }

        return $input;
    }
}
