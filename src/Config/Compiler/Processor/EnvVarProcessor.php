<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Compiler\Processor;

use Smile\GdprDump\Config\Compiler\CompileException;
use Smile\GdprDump\Config\ConfigInterface;

final class EnvVarProcessor implements ProcessorInterface
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
     * Replace environment variable placeholders (e.g. "%env(DB_HOST)%")
     *
     * @throws CompileException
     */
    public function process(ConfigInterface $config): void
    {
        $data = $this->processItem($config->toArray());
        $config->reset($data);
    }

    /**
     * Process a config item.
     *
     * @throws CompileException
     */
    private function processItem(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->processItem($value);
                continue;
            }

            $data[$key] = $this->processValue($value);
        }

        return $data;
    }

    /**
     * Process a config value.
     *
     * @throws CompileException
     */
    private function processValue(mixed $value): mixed
    {
        if (!is_string($value) || !str_starts_with($value, '%env(') || !str_ends_with($value, ')%')) {
            return $value;
        }

        $name = substr($value, 5, -2);
        [$type, $name] = $this->parse($name);

        $value = getenv($name);
        if ($value === false) {
            throw new CompileException(sprintf('The environment variable "%s" is not defined.', $name));
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
     * @throws CompileException
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
            throw new CompileException(
                sprintf('Invalid type "%s". Expected: %s.', $type, implode(', ', $this->types))
            );
        }

        if ($name === '') {
            throw new CompileException('Environment variable name must not be empty.');
        }

        if (!preg_match('/^' . self::VAR_NAME_REGEX . '$/', $name)) {
            throw new CompileException(
                sprintf('"%s" is not a valid environment variable name. Expected format: "[A-Z][A-Z0-9_]*".', $name)
            );
        }

        return [$type, $name];
    }

    /**
     * Decode a JSON-encoded string.
     *
     * @throws CompileException
     */
    private function decodeJson(string $value, string $name): mixed
    {
        $value = json_decode($value, true);

        if ($value === null) {
            throw new CompileException(
                sprintf('Failed to parse the JSON value of the environment variable "%s".', $name)
            );
        }

        return $value;
    }
}
