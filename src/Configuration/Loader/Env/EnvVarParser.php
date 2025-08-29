<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Loader\Env;

use Smile\GdprDump\Configuration\Exception\ParseException;

final class EnvVarParser
{
    /**
     * Environment variable name format.
     */
    private const VAR_NAME_REGEX = '[A-Z][A-Z0-9_]*';

    /**
     * @var string[]
     */
    private array $types = [
        'string',
        'bool',
        'int',
        'float',
        'json',
    ];

    /**
     * Parse a value.
     *
     * @throws ParseException
     */
    public function parse(mixed $value): mixed
    {
        return match (true) {
            is_object($value) => $this->parseObject($value),
            is_array($value) => $this->parseArray($value),
            is_string($value) => $this->parseString($value),
            default => $value,
        };
    }

    /**
     * Parse an object.
     */
    private function parseObject(object $object): object
    {
        foreach (get_object_vars($object) as $property => $value) {
            $object->$property = $this->parse($value);
        }

        return $object;
    }

    /**
     * Parse an array.
     */
    private function parseArray(array $array): array
    {
        foreach ($array as $key => $value) {
            $array[$key] = $this->parse($value);
        }

        return $array;
    }

    /**
     * Parse a string.
     */
    private function parseString(string $value): mixed
    {
        if (!str_starts_with($value, '%env(') || !str_ends_with($value, ')%')) {
            return $value;
        }

        $name = substr($value, 5, -2);
        [$type, $name] = $this->parsePlaceholder($name);

        $value = getenv($name);
        if ($value === false) {
            throw new ParseException(sprintf('The environment variable "%s" is not defined.', $name));
        }

        if ($type === 'json') {
            return $this->decodeJson($value, $name);
        }

        settype($value, $type);

        return $value;
    }

    /**
     * Parse "%env($name)%".
     *
     * @return array{0: string, 1: string}
     */
    private function parsePlaceholder(string $name): array
    {
        $pos = strpos($name, ':');

        if ($pos === false) {
            $type = 'string';
        } else {
            $type = substr($name, 0, $pos);
            $name = substr($name, $pos + 1);
        }

        if (!in_array($type, $this->types, true)) {
            throw new ParseException(
                sprintf('Invalid type "%s". Expected: %s.', $type, implode(', ', $this->types))
            );
        }

        if ($name === '') {
            throw new ParseException('Environment variable name must not be empty.');
        }

        if (!preg_match('/^' . self::VAR_NAME_REGEX . '$/', $name)) {
            throw new ParseException(
                sprintf('"%s" is not a valid environment variable name. Expected format: "[A-Z][A-Z0-9_]*".', $name)
            );
        }

        return [$type, $name];
    }

    /**
     * Decode a JSON-encoded string.
     */
    private function decodeJson(string $value, string $name): mixed
    {
        $value = json_decode($value);

        if ($value === null) {
            throw new ParseException(
                sprintf('Failed to parse the JSON value of the environment variable "%s".', $name)
            );
        }

        return $value;
    }
}
