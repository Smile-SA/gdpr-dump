<?php

declare(strict_types=1);

namespace Smile\GdprDump\Util;

use stdClass;

final class Objects
{
    /**
     * Recursively convert all objects (stdClass) found to arrays.
     */
    public static function toArray(array|stdClass $data): array
    {
        if ($data instanceof stdClass) {
            $data = get_object_vars($data);
        }

        foreach ($data as $key => $value) {
            if ($value instanceof stdClass || is_array($value)) {
                $data[$key] = self::toArray($value);
            }
        }

        return $data;
    }

    /**
     * Merge the properties of an object (stdClass) into another one.
     *
     * Properties of type object are merged recursively, other properties are replaced.
     */
    public static function merge(stdClass $object, stdClass $override): void
    {
        foreach (get_object_vars($override) as $property => $value) {
            $property = (string) $property; // (get_object_vars returns int keys if properties are numeric)

            if (!property_exists($object, $property)) {
                // New property, add it to the existing object
                $object->{$property} = $value instanceof stdClass ? self::deepCloneObject($value) : $value;
                continue;
            }

            if ($object->{$property} instanceof stdClass) {
                if ($value instanceof stdClass) {
                    // Merge values of the two objects
                    self::merge($object->{$property}, $value);
                    continue;
                }

                if ($value === null) {
                    // Allow object removal by setting the value to null
                    unset($object->{$property});
                    continue;
                }
            }

            // Overwrite existing value
            $object->{$property} = $value instanceof stdClass ? self::deepCloneObject($value) : $value;
        }
    }

    /**
     * Deep clone an object (only works with instances of stdClass because their properties are public).
     */
    private static function deepCloneObject(stdClass $object): stdClass
    {
        $clone = clone $object;

        foreach (get_object_vars($clone) as $property => $value) {
            $property = (string) $property; // (get_object_vars returns int keys if properties are numeric)

            if ($value instanceof stdClass) {
                $clone->$property = self::deepCloneObject($value);
            } elseif (is_array($value)) {
                $clone->$property = self::deepCloneArray($value);
            }
        }

        return $clone;
    }

    /**
     * Clone objects contained in an array.
     */
    private static function deepCloneArray(array $array): array
    {
        foreach ($array as $key => $value) {
            if ($value instanceof stdClass) {
                $array[$key] = self::deepCloneObject($value);
            } elseif (is_array($value)) {
                $array[$key] = self::deepCloneArray($value);
            }
        }

        return $array;
    }
}
