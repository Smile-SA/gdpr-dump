<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Config\Validator;

use Smile\GdprDump\Config\Validator\JsonSchemaValidator;
use Smile\GdprDump\Tests\TestCase;

class JsonSchemaValidatorTest extends TestCase
{
    /**
     * @var string
     */
    private $schemaFile;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->schemaFile = $this->getResource('config/schema.json');
    }

    /**
     * Test validaton success.
     */
    public function testValidationSuccess()
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
    public function testValidationError()
    {
        // The property "key" must be a string
        $data = ['key' => 1];

        $validator = new JsonSchemaValidator($this->schemaFile);
        $result = $validator->validate($data);

        $this->assertFalse($result->isValid());
        $this->assertNotEmpty($result->getMessages());
    }

    /**
     * Test if an exception is thrown when the schema file is not found.
     *
     * @expectedException \Smile\GdprDump\Config\Validator\ValidationException
     */
    public function testFileNotFound()
    {
        $schemaFile = 'notExists.json';

        $validator = new JsonSchemaValidator($schemaFile);
        $validator->validate(['key' => 'value']);
    }
}
