<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Processor;

use RuntimeException;
use UnexpectedValueException;

class EnvVarProcessor implements ProcessorInterface
{
    /**
     * Environment variable name format.
     */
    const VAR_NAME_REGEX = '[A-Z][A-Z0-9_]*';

    /**
     * @var string[]
     */
    private $types = [
        'string',
        'bool',
        'int',
        'float',
        'json',
    ];

    /**
     * @inheritdoc
     * @throws RuntimeException
     * @throws UnexpectedValueException
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function process($value)
    {
        if (!is_string($value) || strpos($value, '%env(') !== 0 || substr($value, -2) !== ')%') {
            return $value;
        }

        $name = substr($value, 5, -2);
        list($type, $name) = $this->parse($name);

        if (!array_key_exists($name, $_SERVER)) {
            throw new RuntimeException(sprintf('The environment variable "%s" is not defined.', $name));
        }

        $value = $_SERVER[$name];

        if ($type === 'json') {
            return $this->decodeJson($value, $name);
        }

        settype($value, $type);

        return $value;
    }

    /**
     * Parse "%env($name)%".
     *
     * @param string $name
     * @return array
     * @throws UnexpectedValueException
     * @SuppressWarnings(PHPMD.ElseExpression)
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
            throw new UnexpectedValueException(
                sprintf('Invalid type "%s". Expected: %s.', $type, implode(', ', $this->types))
            );
        }

        if ($name === '') {
            throw new UnexpectedValueException('Environment variable name must not be empty.');
        }

        if (!preg_match('/^' . self::VAR_NAME_REGEX . '$/', $name)) {
            throw new UnexpectedValueException(
                sprintf('"%s" is not a valid environment variable name. Expected format: "[A-Z][A-Z0-9_]*".', $name)
            );
        }

        return [$type, $name];
    }

    /**
     * Decode a JSON-encoded string.
     *
     * @param string $value
     * @param string $name
     * @return mixed
     * @throws RuntimeException
     */
    private function decodeJson(string $value, string $name)
    {
        $value = json_decode($value, true);

        if ($value === null) {
            throw new RuntimeException(
                sprintf('Failed to parse the JSON value of the environment variable "%s".', $name)
            );
        }

        return $value;
    }
}
