<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Validator;

use JsonSchema\Validator;
use Smile\GdprDump\Config\Exception\InvalidJsonSchemaException;
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

        // Validate the data against the schema file
        try {
            $validator->validate($input, (object) ['$ref' => $this->schemaFile]);
        } catch (Throwable $e) {
            throw new InvalidJsonSchemaException($e->getMessage(), $e);
        }

        // Build the messages array
        $messages = [];
        foreach ($validator->getErrors() as $error) {
            $messages[] = $error['property'] !== ''
                ? sprintf('[%s] %s', $error['property'], $error['message'])
                : $error['message'];
        }

        return new ValidationResult(!$messages, $messages);
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
