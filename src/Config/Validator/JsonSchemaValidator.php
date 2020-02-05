<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Validator;

use Exception;
use JsonSchema\Validator;

class JsonSchemaValidator implements ValidatorInterface
{
    /**
     * @var Validator
     */
    private $schemaValidator;

    /**
     * @var string
     */
    private $schemaFile;

    /**
     * @param string $schemaFile
     */
    public function __construct(string $schemaFile)
    {
        // Prefix the file name by the schema
        if (strpos($schemaFile, 'phar://') === false) {
            $schemaFile = 'file://' . $schemaFile;
        }

        $this->schemaFile = $schemaFile;
    }

    /**
     * @inheritdoc
     */
    public function validate($data): ValidationResultInterface
    {
        $validator = $this->getValidator();

        // Automatically convert associative arrays to stdClass (required for object validation)
        if (is_array($data)) {
            $data = json_decode(json_encode($data));
        }

        // Validate the data against the schema file
        try {
            $validator->validate($data, (object) ['$ref' => $this->schemaFile]);
        } catch (Exception $e) {
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
     *
     * @return Validator
     */
    private function getValidator(): Validator
    {
        if ($this->schemaValidator === null) {
            $this->schemaValidator = new Validator();
        }

        return $this->schemaValidator;
    }
}
