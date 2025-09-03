<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Loader;

use Smile\GdprDump\Configuration\Compiler\ConfigurationCompiler;
use Smile\GdprDump\Configuration\Exception\ConfigurationException;
use Smile\GdprDump\Configuration\Exception\ParseException;
use Smile\GdprDump\Configuration\Loader\Resource\Resource;
use Smile\GdprDump\Configuration\Loader\Resource\ResourceFactory;
use Smile\GdprDump\Configuration\Loader\Resource\ResourceParser;
use Smile\GdprDump\Configuration\Loader\Version\VersionApplier;
use Smile\GdprDump\Util\Objects;
use stdClass;
use Throwable;

final class ConfigurationLoader
{
    public function __construct(
        private ResourceParser $resourceParser,
        private ResourceFactory $resourceFactory,
        private VersionApplier $versionApplier,
        private ConfigurationCompiler $compiler,
    ) {
    }


    /**
     * Load the configuration from the specified file or YAML input.
     */
    public function load(string $input, bool $isFile = true): Container
    {
        $container = new Container();

        try {
            $resource = $isFile
                ? $this->resourceFactory->createFileResource($input)
                : $this->resourceFactory->createStringResource($input);

            // Load the resource into the container
            $this->loadResource($container, $resource);

            //
            if ($container->has('fail')) {
                throw new ParseException((string) $container->get('fail'));
            }

            // Remove config properties that are only useful during parsing
            $this->removeInternalProperties($container);

            // Compile the configuration (e.g. resolves environment variables)
            $this->compiler->compile($container);
        } catch (Throwable $e) {
            throw $e instanceof ConfigurationException ? $e : new ParseException($e->getMessage(), $e);
        }

        return $container;
    }

    private function loadResource(
        Container $container,
        Resource $resource,
        array &$loadedFiles = [],
        ?string &$version = null
    ): void {
        // Parse the resource
        $parsed = $this->resourceParser->parse($resource);

        // Detect the application version
        $version ??= $this->versionApplier->detectVersion($parsed);

        // Check if there are files to import (from the `extends` parameter)
        foreach ($this->getImports($parsed, $version) as $fileName) {
            $import = $this->resourceFactory->createFileResource($fileName, $this->getCurrentDirectory($resource));

            // Load the file only if it wasn't already included
            if (!in_array($import->getInput(), $loadedFiles, true)) {
                $loadedFiles[] = $import->getInput();
                $this->loadResource($container, $import, $loadedFiles, $version);
            }
        }

        var_dump($resource->getInput());

        // Merge `if_version` blocks that match the detected version
        $this->versionApplier->applyVersion($parsed, $version);

        // Merge the parsed data into the main configuration
        Objects::merge($container->getRoot(), $parsed);
    }

    /**
     * Remove config properties that are only useful during parsing.
     */
    private function removeInternalProperties(Container $container): void
    {
        $container->remove('extends')
            ->remove('fail')
            ->remove('if_version')
            ->remove('requires_vesion') // deprecated param
            ->remove('version');
    }

    /**
     * Get all files declared in the `extends` parameter.
     */
    private function getImports(stdClass $parsed, ?string $version)
    {
        $imports = isset($parsed->extends) ? (array) $parsed->extends : [];

        // Also get the imports declared in `if_version` sections
        $versionedImports = $this->versionApplier->getImports($parsed, $version);
        if ($versionedImports) {
            $imports = array_merge($imports, $versionedImports);
        }

        return $imports;
    }

    /**
     * Get the current working directory (relative to the specified resource if it is a file).
     */
    private function getCurrentDirectory(Resource $resource): ?string
    {
        if ($resource->isFile()) {
            return dirname($resource->getInput());
        }

        $currentDirectory = getcwd();

        return $currentDirectory !== false ? $currentDirectory : null;
    }
}
