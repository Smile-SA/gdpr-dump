<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration;

use Smile\GdprDump\Configuration\Exception\ConfigurationException;
use Smile\GdprDump\Configuration\Loader\ConfigurationLoader;
use Smile\GdprDump\Configuration\Loader\Container;
use Smile\GdprDump\Configuration\Loader\Resource\Resource;
use Smile\GdprDump\Configuration\Loader\Resource\ResourceFactory;
use Smile\GdprDump\Configuration\Mapper\ConfigurationMapper;

final class ConfigurationBuilder
{
    public function __construct(
        private ConfigurationLoader $configurationLoader,
        private ConfigurationMapper $configurationMapper,
    ) {
    }

    /**
     * Load the configuration from registered resources and return a Configuration object.
     *
     * @throws ConfigurationException
     */
    public function build(?string $input = null, bool $isFile = true): Configuration
    {
        if ($input === null) {
            return new Configuration(); // Nothing to load
        }

        $container = $this->configurationLoader->load($input, $isFile);

        // Convert the container to a configuration object with getters/setters
        return $this->configurationMapper->fromArray($container->toArray());
    }
}
