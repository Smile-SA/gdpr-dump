<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Builder;

use Smile\GdprDump\Config\Builder\ConfigBuilder;
use Smile\GdprDump\Config\ConfigException;
use Smile\GdprDump\Config\Validator\JsonSchemaValidator;
use Smile\GdprDump\Tests\Unit\TestCase;
use Symfony\Component\Yaml\Yaml;

class ConfigBuilderTest extends TestCase
{
    /**
     * Assert that the builder creates a yaml file.
     */
    public function testBuilder(): void
    {
        $data = ['key' => 'value',];
        $fileName = $this->getResource('var/test-builder.yaml');
        $builder = $this->createBuilder();
        $builder->build($fileName, $data);
        $this->assertFileExists($fileName);

        $contents = Yaml::parseFile($fileName);
        $this->assertEquals($data, $contents);
    }

    /**
     * Assert that an exception is thrown when the file is not writable.
     */
    public function testFileNotWritable(): void
    {
        $this->expectException(ConfigException::class);
        $this->createBuilder()->build('/not/exists/config.yaml', ['key' => 'value']);
    }

    /**
     * Assert that an exception is thrown when the input data is invalid.
     */
    public function testInvalidData(): void
    {
        $this->expectException(ConfigException::class);
        $this->createBuilder()->build($this->getResource('var/test-builder.yaml'), ['invalidKey' => 'value']);
    }

    /**
     * Create a config builder.
     */
    private function createBuilder(): ConfigBuilder
    {
        return new ConfigBuilder(new JsonSchemaValidator($this->getResource('config/schema.json')));
    }
}
