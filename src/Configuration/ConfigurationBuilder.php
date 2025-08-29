<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration;

use Smile\GdprDump\Configuration\Exception\ConfigurationException;
use Smile\GdprDump\Configuration\Loader\ConfigurationLoader;
use Smile\GdprDump\Configuration\Loader\Resource\Resource;
use Smile\GdprDump\Configuration\Mapper\ConfigurationMapper;
use Smile\GdprDump\Configuration\Validator\JsonSchemaValidator;
use Smile\GdprDump\Util\Objects;

final class ConfigurationBuilder
{
    /**
     * @var Resource[]
     */
    private array $resources = [];

    public function __construct(
        private ConfigurationLoader $configurationLoader,
        private ConfigurationMapper $configurationMapper,
        private JsonSchemaValidator $schemaValidator,
    ) {
    }

    /**
     * Add a resource to the builder.
     */
    public function addResource(Resource $resource): self
    {
        $this->resources[] = $resource;

        return $this;
    }

    /**
     * Load the configuration from registered resources and return a Configuration object.
     *
     * @throws ConfigurationException
     */
    public function build(): Configuration
    {
        // Build a stdClass object that contains the merged data of the provided resources
        $configData = $this->configurationLoader->load(...$this->resources);

        // Validate the configuration data against a JSON schema
        $this->schemaValidator->validate($configData);

        // Build and return an object representation of the configuration data
        return $this->configurationMapper->fromArray(Objects::toArray($configData));
    }
}
