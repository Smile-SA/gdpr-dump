<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration;

use Smile\GdprDump\Configuration\Loader\ConfigurationLoader;
use Smile\GdprDump\Configuration\Loader\Resource\Resource;
use Smile\GdprDump\Configuration\Loader\Resource\ResourceFactory;
use Smile\GdprDump\Configuration\Mapper\ConfigurationMapper;

final class ConfigurationFactory
{
    public function __construct(
        private ConfigurationLoader $configurationLoader,
        private ConfigurationMapper $configurationMapper,
        private ResourceFactory $resourceFactory,
    ) {
    }

    /**
     * Create a config builder.
     */
    public function createBuilder(): ConfigurationBuilder
    {
        return new ConfigurationBuilder($this->configurationLoader, $this->configurationMapper);
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
    public function createStringResource(string $input): Resource
    {
        return $this->resourceFactory->createStringResource($input);
    }
}
