<?php
declare(strict_types=1);

namespace Smile\GdprDump\Converter\Helper;

class ArrayHelper
{
    /**
     * Check whether the path exists in the array.
     *
     * @param array $array
     * @param string $path
     * @param mixed|null $default
     * @return mixed
     */
    public static function getPath(array $array, string $path, $default = null)
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
     *
     * @param array $array
     * @param string $path
     * @param mixed $value
     */
    public static function setPath(array &$array, string $path, $value)
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
}
