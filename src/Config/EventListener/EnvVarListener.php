<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\EventListener;

use Smile\GdprDump\Config\Event\ParseEvent;
use Smile\GdprDump\Config\Validator\ValidationException;

final class EnvVarListener
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
     * Replace environment variable placeholders (e.g. "%env(DB_HOST)%").
     */
    public function __invoke(ParseEvent $event): void
    {
        $config = $event->getConfig();
        $data = $this->processItem($config->toArray());
        $config->reset($data);
    }

    /**
     * Process a config item.
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
            throw new ValidationException(sprintf('The environment variable "%s" is not defined.', $name));
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
            throw new ValidationException(
                sprintf('Invalid type "%s". Expected: %s.', $type, implode(', ', $this->types))
            );
        }

        if ($name === '') {
            throw new ValidationException('Environment variable name must not be empty.');
        }

        if (!preg_match('/^' . self::VAR_NAME_REGEX . '$/', $name)) {
            throw new ValidationException(
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
        $value = json_decode($value, true);

        if ($value === null) {
            throw new ValidationException(
                sprintf('Failed to parse the JSON value of the environment variable "%s".', $name)
            );
        }

        return $value;
    }
}
