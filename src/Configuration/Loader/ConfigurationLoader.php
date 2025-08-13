<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Loader;

use Smile\GdprDump\Configuration\Exception\ParseException;
use Smile\GdprDump\Configuration\Loader\Processor\Processor;
use Smile\GdprDump\Configuration\Loader\Resource\Resource;
use Smile\GdprDump\Configuration\Loader\Resource\ResourceFactory;
use Smile\GdprDump\Configuration\Loader\Resource\ResourceParser;
use Smile\GdprDump\Configuration\Loader\Version\VersionApplier;
use Smile\GdprDump\Util\Objects;
use stdClass;
use Throwable;

final class ConfigurationLoader
{
    /**
     * @param Processor[] $processors
     */
    public function __construct(
        private ResourceParser $resourceParser,
        private ResourceFactory $resourceFactory,
        private VersionApplier $versionApplier,
        private iterable $processors,
    ) {
    }

    /**
     * Load the configuration from the specified resources (files or string input).
     *
     * @throws ParseException
     */
    public function load(Resource ...$resources): stdClass
    {
        $parsed = new stdClass();

        try {
            if ($resources) {
                $this->loadResources($resources, $parsed);
            }

            $this->runProcessors($parsed);
        } catch (Throwable $e) {
            throw $e instanceof ParseException ? $e : new ParseException($e->getMessage(), $e);
        }

        return $parsed;
    }

    /**
     * Load the specified resources and return an object representation of the configuration (stdClass).
     *
     * Parse order of resources is LIFO, merge order is FIFO.
     *
     * For example, if the following resources were registered:
     * - config1.yaml (extends parent1_1.yaml and parent1_2.yaml)
     * - config2.yaml
     *
     * The parse order is config2.yaml, config1.yaml, parent1_2.yaml, parent1_1.yaml.
     * The merge order is the other way around (from parent_1_1.yaml to config2.yaml).
     *
     * This order allows to properly detect some settings during parsing (e.g. application version),
     * and then to merge files in the correct order afterwards.
     *
     * @param Resource[] $resources resources to load
     * @param stdClass $configuration the object that will contain the parsed configuration
     * @param string[] $loadedTemplates cache for files defined in the `extends` parameter, must be loaded only once
     * @param string $version the application version, it it used to merge `if_version` blocks
     */
    private function loadResources(
        array &$resources,
        stdClass $configuration,
        array &$loadedTemplates = [],
        ?string &$version = null,
    ): void {
        // Parse the resource (LIFO)
        /** @var Resource $resource (array_pop never returns null in this context) */
        $resource = array_pop($resources);
        $parsed = $this->resourceParser->parse($resource);

        // Detect the application version
        $version ??= $this->versionApplier->detectVersion($parsed);

        // Add files declared in the `extends` parameter to the $resources array
        if (isset($parsed->extends)) {
            $fileNames = (array) $parsed->extends;
            $currentDirectory = $this->getCurrentDirectory($resource);
            $this->addParentFiles($fileNames, $resources, $loadedTemplates, $currentDirectory);
            unset($parsed->extends);
        }

        // Load remaining resources
        if ($resources) {
            $this->loadResources($resources, $configuration, $loadedTemplates, $version);
        }

        // Merge if_version blocks
        $this->versionApplier->applyVersion($parsed, (string) $version);

        // Merge the parsed data into the main configuration (FIFO, because it is done after the recursive call)
        Objects::merge($configuration, $parsed);
    }

    /**
     * Add the specified configuration files to the list of resources to parse.
     *
     * @param string[] $fileNames
     * @param Resource[] $resources
     * @param string[] $loadedTemplates
     */
    private function addParentFiles(
        array $fileNames,
        array &$resources,
        array &$loadedTemplates,
        ?string $currentDirectory,
    ): void {
        foreach ($fileNames as $fileName) {
            $resource = $this->resourceFactory->createFileResource($fileName, $currentDirectory);

            // Load a parent file only if it wasn't already included by another config file
            if (!in_array($resource->getInput(), $loadedTemplates, true)) {
                $loadedTemplates[] = $resource->getInput();
                $resources[] = $resource;
            }
        }
    }

    /**
     * Run registered processors on the parsed configuration.
     */
    private function runProcessors(stdClass $configuration): void
    {
        foreach ($this->processors as $processor) {
            $processor->process($configuration);
        }
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
