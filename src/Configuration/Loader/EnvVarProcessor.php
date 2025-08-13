<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Loader;

use Smile\GdprDump\Configuration\Exception\ParseException;

final class EnvVarProcessor
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
     * Process an item.
     *
     * @throws ParseException
     */
    public function process(mixed $value): mixed
    {
        return match (true) {
            is_object($value) => $this->processObject($value),
            is_array($value) => $this->processArray($value),
            is_string($value) => $this->processValue($value),
            default => $value,
        };
    }

    /**
     * Process an object.
     */
    private function processObject(object $object): object
    {
        foreach (get_object_vars($object) as $property => $value) {
            $object->$property = $this->process($value);
        }

        return $object;
    }

    /**
     * Process an array.
     */
    private function processArray(array $array): array
    {
        foreach ($array as $key => $value) {
            $array[$key] = $this->process($value);
        }

        return $array;
    }

    /**
     * Process a string value.
     *
     * @throws CompileException
     */
    public function processValue(string $value): mixed
    {
        if (!str_starts_with($value, '%env(') || !str_ends_with($value, ')%')) {
            return $value;
        }

        $name = substr($value, 5, -2);
        [$type, $name] = $this->parse($name);

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
    private function parse(string $name): array
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
