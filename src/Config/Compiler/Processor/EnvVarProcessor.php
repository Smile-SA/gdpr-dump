<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Compiler\Processor;

use Smile\GdprDump\Config\Compiler\CompileException;
use Smile\GdprDump\Config\ConfigInterface;

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
     */
    public function process(ConfigInterface $config): void
    {
        $data = $this->processItem($config->toArray());
        $config->reset($data);
    }

    /**
     * Process a config item.
     *
     * @param array $data
     * @return array
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
     * @param mixed $value
     * @return mixed
     * @throws CompileException
     */
    private function processValue($value)
    {
        if (!is_string($value) || strpos($value, '%env(') !== 0 || substr($value, -2) !== ')%') {
            return $value;
        }

        $name = substr($value, 5, -2);
        list($type, $name) = $this->parse($name);

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
     * @param string $name
     * @return array
     * @throws CompileException
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
     * @param string $value
     * @param string $name
     * @return mixed
     * @throws CompileException
     */
    private function decodeJson(string $value, string $name)
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
