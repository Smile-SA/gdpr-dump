<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Validator;

use JsonSchema\Validator;
use Smile\GdprDump\Config\Exception\InvalidJsonSchemaException;
use Smile\GdprDump\Config\Loader\ContainerInterface;
use Throwable;

final class JsonSchemaValidator implements SchemaValidator
{
    private string $schemaFile;
    private ?Validator $schemaValidator = null;

    public function __construct(?string $schemaFile = null)
    {
        $schemaFile ??= dirname(__DIR__, 3) . '/app/config/schema.json';

        // Prefix the file name with the schema
        if (!str_contains($schemaFile, 'phar://')) {
            $schemaFile = 'file://' . $schemaFile;
        }

        $this->schemaFile = $schemaFile;
    }

    public function validate(array|object $input): ValidationResult
    {
        $validator = $this->getValidator();
        //$object = $this->containerToStdClass($dataObject); // TODO remove

        // Validate the data against the schema file
        try {
            $validator->validate($input, (object) ['$ref' => $this->schemaFile]);
        } catch (Throwable $e) {
            throw new InvalidJsonSchemaException($e->getMessage(), $e);
        }

        // Build the messages array
        $messages = [];
        foreach ($validator->getErrors() as $error) {
            if ($this->isErrorAllowed($error)) {
                // Allow setting an object to null (removes an entry defined by a parent file)
                continue;
            }

            $messages[] = $error['property'] !== ''
                ? sprintf('[%s] %s', $error['property'], $error['message'])
                : $error['message'];
        }

        return new ValidationResult(!$messages, $messages);
    }

    /**
     * Check whether a validation error should be ignored.
     *
     * @param array{
     *     property: string,
     *     pointer: string,
     *     message: string,
     *     constraint: array{name: string, params: mixed[]}
     * }
     */
    private function isErrorAllowed(array $error)
    {
        $params = $error['constraint']['params'];

        return $error['constraint']['name'] === 'type'
            && array_key_exists('found', $params) && $params['found'] === 'NULL'
            && array_key_exists('expected', $params) && $params['expected'] === 'an object';
    }

    // TODO REMOVE
    private function containerToStdClass(ContainerInterface $container): object
    {
        $object = new \stdClass();

        foreach ($container->toArray() as $key => $value) {
            if ($value instanceof ContainerInterface) {
                $value = $this->containerToStdClass($value);
            } elseif (is_array($value)) {
                $value = $this->arrayToStdClass($value);
            }

            $object->{$key} = $value;
        }

        return $object;
    }

    private function arrayToStdClass(array $array): array
    {
        foreach ($array as $key => $value) {
            if ($value instanceof ContainerInterface) {
                $array[$key] = $this->containerToStdClass($value);
            } elseif (is_array($value)) {
                $array[$key] = $this->arrayToStdClass($value);
            }
        }

        return $array;
    }

    /**
     * Get the JSON schema validator.
     */
    private function getValidator(): Validator
    {
        $this->schemaValidator ??= new Validator();

        return $this->schemaValidator;
    }
}
