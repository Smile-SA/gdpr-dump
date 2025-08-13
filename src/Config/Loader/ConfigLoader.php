<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Loader;

use Smile\GdprDump\Config\Event\ConfigLoadedEvent;
use Smile\GdprDump\Config\Event\ConfigLoadEvent;
use Smile\GdprDump\Config\Event\MergeConfigEvent;
use Smile\GdprDump\Config\Event\ParseConfigEvent;
use Smile\GdprDump\Config\Exception\ConfigLoadException;
use Smile\GdprDump\Config\Exception\JsonSchemaValidationException;
use Smile\GdprDump\Config\Loader\Locator\FileLocator;
use Smile\GdprDump\Config\Parser\ParserResolver;
use Smile\GdprDump\Config\Resource\FileResource;
use Smile\GdprDump\Config\Resource\Resource;
use Smile\GdprDump\Config\Resource\ResourceStack;
use Smile\GdprDump\Config\Validator\SchemaValidator;
use Smile\GdprDump\Util\Objects;
use stdClass;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Throwable;

final class ConfigLoader implements Loader
{
    private ResourceStack $resources;

    public function __construct(
        private FileLocator $fileLocator,
        private ParserResolver $parserResolver,
        private SchemaValidator $validator,
        private EventDispatcherInterface $eventDispatcher,
    ) {
        $this->resources = new ResourceStack();
    }

    public function addResource(Resource $resource): self
    {
        if ($resource instanceof FileResource) {
            $resource = new FileResource($this->fileLocator->locate($resource->getInput()));
        }

        $this->resources->push($resource);

        return $this;
    }

    public function load(): object
    {
        $loadedFiles = [];
        $parsed = new stdClass();

        try {
            $this->eventDispatcher->dispatch(new ConfigLoadEvent());
            $this->loadResources(clone $this->resources, $parsed, $loadedFiles);

            $result = $this->validator->validate($parsed);
            if (!$result->isValid()) {
                throw new JsonSchemaValidationException($result->getMessages());
            }

            $this->eventDispatcher->dispatch(new ConfigLoadedEvent($parsed));
        } catch (ConfigLoadException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new ConfigLoadException($e->getMessage(), $e);
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
     */
    private function loadResources(ResourceStack $resources, stdClass $dataObject, array &$loadedFiles): void
    {
        // Parse the resource (LIFO)
        $resource = $resources->pop();
        $parsed = $this->parserResolver
            ->getParser($resource)
            ->parse($resource);

        var_dump('PARSE: ' . $resource->getInput());
        $this->eventDispatcher->dispatch(new ParseConfigEvent($parsed));

        // Load parent files (if any)
        if (isset($parsed->extends)) {
            $fileNames = (array) $parsed->extends;
            $currentDirectory = $this->getCurrentDirectory($resource);
            $this->addParentResources($fileNames, $resources, $loadedFiles, $currentDirectory);
            unset($parsed->extends);
        }

        // Load other resources that were registered in the stack (LIFO))
        while (!$resources->isEmpty()) {
            $this->loadResources($resources, $dataObject, $loadedFiles);
        }

        // Merge the parsed data to the provided object (FIFO, because it is done after the recursive call)
        var_dump('MERGE: ' . $resource->getInput() . '   ' . json_encode($parsed));
        $this->eventDispatcher->dispatch(new MergeConfigEvent($parsed));
        Objects::merge($dataObject, $parsed);
    }

    /**
     * Add the specified files to the resource stack that is used by the parsing function.
     */
    private function addParentResources(
        array $fileNames,
        ResourceStack $resources,
        array &$loadedFiles,
        string $currentDirectory,
    ): void {
        foreach ($fileNames as $fileName) {
            $fileName = $this->fileLocator->locate($fileName, $currentDirectory);
            if (!in_array($fileName, $loadedFiles, true)) {
                $loadedFiles[] = $fileName;
                $resources->push(new FileResource($fileName));
            }
        }
    }

    /**
     * Get the current working directory (relative to the specified resource if it is a file).
     */
    private function getCurrentDirectory(Resource $resource): string
    {
        return $resource instanceof FileResource
            ? dirname($resource->getInput())
            : getcwd();
    }
}
