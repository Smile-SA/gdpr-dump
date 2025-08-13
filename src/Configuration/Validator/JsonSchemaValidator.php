<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Validator;

use JsonSchema\Validator;
use Smile\GdprDump\Configuration\Exception\JsonSchemaException;
use Smile\GdprDump\Configuration\Exception\ParseException;
use Smile\GdprDump\Util\Platform;
use stdClass;
use Throwable;

final class JsonSchemaValidator
{
    private ?Validator $schemaValidator = null;

    public function __construct(private string $schemaFile)
    {
        if (!Platform::isAbsolutePath($schemaFile)) {
            $schemaFile = dirname(__DIR__, 3) . '/' . $schemaFile;
        }

        // Prefix the file name with the schema
        if (!str_contains($schemaFile, '://')) {
            $schemaFile = 'file://' . $schemaFile;
        }

        $this->schemaFile = $schemaFile;
    }

    /**
     * Validate an array or object that represents the configuration.
     *
     * @throws JsonSchemaException if the provided input is invalid against the JSON schema
     * @throws ParseException if an unexpected error occurred during validation
     */
    public function validate(stdClass $input): void
    {
        $validator = $this->getValidator();

        // Validate the data against the schema file
        try {
            $validator->validate($input, (object) ['$ref' => $this->schemaFile]);
        } catch (Throwable $e) {
            throw new ParseException($e->getMessage(), $e);
        }

        if (!$validator->isValid()) {
            throw new JsonSchemaException($this->prepareMessages($validator));
        }
    }

    /**
     * Return an array containing all error messages.
     */
    private function prepareMessages(Validator $validator): array
    {
        $messages = [];
        foreach ($validator->getErrors() as $error) {
            $messages[] = $error['property'] !== ''
                ? sprintf('[%s] %s', $error['property'], $error['message'])
                : $error['message'];
        }

        return $messages;
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
