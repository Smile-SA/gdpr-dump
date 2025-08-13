<?php

declare(strict_types=1);

namespace Smile\GdprDump\Util;

use UnexpectedValueException;

// TODO implement unit test
final class Objects
{
    /**
     * Recursively convert all objects found to arrays.
     */
    public static function toArray(array|object $data)
    {
        if (is_object($data)) {
            $data = (array) $data;
        }

        foreach ($data as $key => $value) {
            if (is_object($value) || is_array($value)) {
                $data[$key] = self::toArray($value);
            }
        }

        return $data;
    }

    /**
     * Merge the properties of an object into another one.
     *
     * Properties of type object are merged recursively, other properties are replaced.
     */
    public static function merge(object $object, object $override): void
    {
        foreach ($override as $property => $value) {
            if (!property_exists($object, $property)) {
                // New property, add it to the existing object
                $object->{$property} = $value;
                continue;
            }

            if (is_object($object->{$property})) {
                if (is_object($value)) {
                    // Merge values of the two objects
                    self::merge($object->{$property}, $value);

                    // If the merged object is empty, remove it (generates a cleaner config)
                    if (/*(array) $value && */!((array) $object->{$property})) { // TODO
                        unset($object->{$property});
                    }
                    continue;
                }

                if ($value === null) {
                    // Allow object removal by setting the value to null
                    unset($object->{$property});
                    continue;
                }
            }

            // Overwrite existing value
            $object->{$property} = $value;
        }
    }

    /**
     * Get the property value of an object.
     */
    public static function getProperty(object $object, string $property, ?string $expectedType = null): mixed
    {
        if (!property_exists($object, $property)) {
            return null;
        }

        $value = $object->$property;
        if ($expectedType === null) {
            return $value;
        }

        $nullable = false;

        $expectedType = match ($expectedType) {
            'bool' => 'boolean',
            'int' => 'integer',
            default => $expectedType,
        };

        if (str_starts_with($expectedType, '?')) {
            $nullable = true;
            $expectedType = substr($expectedType, 1);
        }

        if (!$nullable && $value === null || gettype($value) !== $expectedType) {
            throw new UnexpectedValueException(sprintf('The property "%s" must be of type "%s".', $property, $expectedType));
        }

        return $value;
    }
}
