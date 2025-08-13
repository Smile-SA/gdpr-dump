<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Configuration\Validator;

use Smile\GdprDump\Configuration\Exception\JsonSchemaException;
use Smile\GdprDump\Configuration\Exception\ParseException;
use Smile\GdprDump\Configuration\Validator\JsonSchemaValidator;
use Smile\GdprDump\Tests\Unit\TestCase;
use stdClass;

final class JsonSchemaValidatorTest extends TestCase
{
    private string $schemaFile;

    protected function setUp(): void
    {
        $this->schemaFile = $this->getResource('test_schema.json');
    }

    /**
     * Test validaton success.
     */
    public function testValidationSuccess(): void
    {
        $data = (object) ['key' => 'value'];

        $validator = new JsonSchemaValidator($this->schemaFile);
        $validator->validate($data);
        $this->expectNotToPerformAssertions();
    }

    /**
     * Test validation error.
     */
    public function testValidationError(): void
    {
        $data = new stdClass();
        $data->key = 1;

        $validator = new JsonSchemaValidator($this->schemaFile);
        $this->expectException(JsonSchemaException::class);
        $validator->validate($data);
    }

    /**
     * Assert that an exception is thrown when the schema file is not found.
     */
    public function testFileNotFound(): void
    {
        $schemaFile = 'not_exists.json';
        $validator = new JsonSchemaValidator($schemaFile);

        $this->expectException(ParseException::class);
        $validator->validate((object) ['key' => 'value']);
    }
}
