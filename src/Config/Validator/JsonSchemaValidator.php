<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Validator;

use JsonSchema\Validator;
use Throwable;

final class JsonSchemaValidator implements ValidatorInterface
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

    public function validate(mixed $data): ValidationResultInterface
    {
        $validator = $this->getValidator();

        // Automatically convert associative arrays to stdClass (required for object validation)
        if (is_array($data)) {
            $data = json_decode((string) json_encode($data));
        }

        // Validate the data against the schema file
        try {
            $validator->validate($data, (object) ['$ref' => $this->schemaFile]);
        } catch (Throwable $e) {
            throw new ValidationException($e->getMessage(), $e);
        }

        // Build the messages array
        $messages = [];
        foreach ($validator->getErrors() as $error) {
            $messages[] = $error['property'] !== ''
                ? sprintf('[%s] %s', $error['property'], $error['message'])
                : $error['message'];
        }

        // Create the validation results object
        $result = new ValidationResult();
        $result->setValid($validator->isValid());
        $result->setMessages($messages);

        return $result;
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
