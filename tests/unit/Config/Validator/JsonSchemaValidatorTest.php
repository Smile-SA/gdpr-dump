<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Validator;

use Smile\GdprDump\Config\Validator\JsonSchemaValidator;
use Smile\GdprDump\Config\Validator\ValidationException;
use Smile\GdprDump\Tests\Unit\TestCase;

final class JsonSchemaValidatorTest extends TestCase
{
    private string $schemaFile;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->schemaFile = $this->getResource('config/schema.json');
    }

    /**
     * Test validaton success.
     */
    public function testValidationSuccess(): void
    {
        $data = ['key' => 'value'];

        $validator = new JsonSchemaValidator($this->schemaFile);
        $result = $validator->validate($data);

        $this->assertTrue($result->isValid());
        $this->assertEmpty($result->getMessages());
    }

    /**
     * Test validation error.
     */
    public function testValidationError(): void
    {
        // The property "key" must be a string
        $data = ['key' => 1];

        $validator = new JsonSchemaValidator($this->schemaFile);
        $result = $validator->validate($data);

        $this->assertFalse($result->isValid());
        $this->assertNotEmpty($result->getMessages());
    }

    /**
     * Assert that an exception is thrown when the schema file is not found.
     */
    public function testFileNotFound(): void
    {
        $schemaFile = 'not_exists.json';
        $validator = new JsonSchemaValidator($schemaFile);

        $this->expectException(ValidationException::class);
        $validator->validate(['key' => 'value']);
    }
}
