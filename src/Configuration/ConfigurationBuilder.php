<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration;

use Smile\GdprDump\Configuration\Exception\ConfigurationException;
use Smile\GdprDump\Configuration\Loader\ConfigurationLoader;
use Smile\GdprDump\Configuration\Loader\Container;
use Smile\GdprDump\Configuration\Loader\Resource\Resource;
use Smile\GdprDump\Configuration\Mapper\ConfigurationMapper;

final class ConfigurationBuilder
{
    /**
     * @var Resource[]
     */
    private array $resources = [];

    public function __construct(
        private ConfigurationLoader $configurationLoader,
        private ConfigurationMapper $configurationMapper,
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
        $container = new Container();

        // Add the registered resources to the container
        $this->configurationLoader->load($container, ...$this->resources);

        // Convert the container to a configuration object with getters/setters
        return $this->configurationMapper->fromArray($container->toArray());
    }
}
