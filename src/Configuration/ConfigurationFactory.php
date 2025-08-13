<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration;

use Smile\GdprDump\Configuration\Resource\Resource;
use Smile\GdprDump\Configuration\Resource\ResourceFactory;
use Smile\GdprDump\Configuration\Validator\JsonSchemaValidator as ValidatorJsonSchemaValidator;
use Smile\GdprDump\Configuration\Validator\Schema\JsonSchemaValidator;

final class ConfigurationFactory
{
    public function __construct(
        private ConfigurationParser $configurationParser,
        private ValidatorJsonSchemaValidator $schemaValidator,
        private ResourceFactory $resourceFactory,
    ) {
    }

    /**
     * Create a config builder.
     */
    public function createBuilder(): ConfigurationBuilder
    {
        return new ConfigurationBuilder($this->configurationParser, $this->schemaValidator);
    }

    /**
     * Create a file resource object. File extension is automatically detected.
     */
    public function createFileResource(string $fileName): Resource
    {
        return $this->resourceFactory->createFileResource($fileName);
    }

    /**
     * Create a JSON resource object.
     */
    public function createJsonResource(string $input): Resource
    {
        return $this->resourceFactory->createJsonResource($input);
    }
}
