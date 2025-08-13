<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration;

use Smile\GdprDump\Configuration\Event\ConfigParsedEvent;
use Smile\GdprDump\Configuration\Event\MergeResourceEvent;
use Smile\GdprDump\Configuration\Event\ParseResourceEvent;
use Smile\GdprDump\Configuration\Parser\ParserResolver;
use Smile\GdprDump\Configuration\Resource\Resource;
use Smile\GdprDump\Configuration\Resource\ResourceFactory;
use Smile\GdprDump\Configuration\Validator\JsonSchemaValidator;
use Smile\GdprDump\Configuration\Exception\ParseException;
use Smile\GdprDump\Util\Objects;
use stdClass;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Throwable;

final class ConfigurationParser
{
    public function __construct(
        private ParserResolver $parserResolver,
        private ResourceFactory $resourceFactory,
        private JsonSchemaValidator $schemaValidator,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * Load the configuration from registered resources.
     *
     * @throws ParseException
     */
    public function parse(Resource ...$resources): stdClass
    {
        $parsed = new stdClass();

        try {
            if ($resources) {
                $this->loadResources($resources, $parsed);
            }

            $this->eventDispatcher->dispatch(new ConfigParsedEvent($parsed));
        } catch (Throwable $e) {
            throw $e instanceof ParseException ? $e : new ParseException($e->getMessage(), $e);
        }

        return $parsed;
    }

    /**
     * Merge the resources that were added to this loader.
     *
     * Parse order is LIFO, merge order is FIFO.
     *
     * For example, if the following resources were registered:
     * - config1.yaml (extends parent1_1.yaml and parent1_2.yaml)
     * - config2.yaml
     *
     * The parse order is config2.yaml, config1.yaml, parent1_2.yaml, parent1_1.yaml.
     * The merge order the other way around (from parent_1_1.yaml to config2.yaml).
     *
     * This allows event listeners:
     * - For ParseFileEvent: to properly prepare some data (e.g. version detection from "version" parameter)
     * - For MergeFileEvent: to properly merge data from ancestor to child (e.g. merge of "if_version" parameter)
     *
     * @param Resource[] $resources
     * @param string[] $loadedFiles
     */
    private function loadResources(
        array &$resources,
        stdClass $container,
        array &$loadedFiles = [],
        ?Resource $first = null
    ): void {
        // Parse the resource (LIFO)
        $resource = array_pop($resources);
        $parsed = $this->parserResolver
            ->getParser($resource)
            ->parse($resource);

        if (!$first) {
            $first = $resource;
        }

        var_dump('PARSE: ' . $resource->getInput());
        $this->eventDispatcher->dispatch(new ParseResourceEvent($container, $parsed, $resource === $first));

        // Load parent files (if any)
        if (isset($parsed->extends)) {
            $fileNames = (array) $parsed->extends;
            $currentDirectory = $this->getCurrentDirectory($resource);
            $this->addParentResources($fileNames, $resources, $loadedFiles, $currentDirectory);
            unset($parsed->extends);
        }

        // Load other resources that were registered in the stack (LIFO))
        if ($resources) {
            $this->loadResources($resources, $container, $loadedFiles, $first);
        }

        // Merge the parsed data to the provided object (FIFO, because it is done after the recursive call)
        var_dump('MERGE: ' . $resource->getInput());
        $this->eventDispatcher->dispatch(new MergeResourceEvent($container, $parsed, $resource === $first));
        Objects::merge($container, $parsed);
    }

    /**
     * Add the specified files to the resource stack that is used by the parsing function.
     *
     * @param string[] $fileNames
     * @param Resource[] $resources
     * @param string[] $loadedFiles
     */
    private function addParentResources(
        array $fileNames,
        array &$resources,
        array &$loadedFiles,
        string $currentDirectory,
    ): void {
        foreach ($fileNames as $fileName) {
            $resource = $this->resourceFactory->createFileResource($fileName, $currentDirectory);
            if (!in_array($resource->getInput(), $loadedFiles, true)) {
                $loadedFiles[] = $resource->getInput();
                $resources[] = $resource;
            }
        }
    }

    /**
     * Get the current working directory (relative to the specified resource if it is a file).
     */
    private function getCurrentDirectory(Resource $resource): string
    {
        return $resource->isFile()
            ? dirname($resource->getInput())
            : getcwd();
    }
}
