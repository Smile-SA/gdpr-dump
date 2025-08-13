<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Loader;

use Smile\GdprDump\Config\ConfigException;
use Smile\GdprDump\Config\Event\ConfigLoadedEvent;
use Smile\GdprDump\Config\Event\MergeConfigEvent;
use Smile\GdprDump\Config\Event\ParseConfigEvent;
use Smile\GdprDump\Config\Exception\ConfigLoadException;
use Smile\GdprDump\Config\Exception\JsonSchemaValidationException;
use Smile\GdprDump\Config\Exception\ParserNotFoundException;
use Smile\GdprDump\Config\Parser\Enum\Format;
use Smile\GdprDump\Config\Parser\Enum\Formats;
use Smile\GdprDump\Config\Parser\Parser;
use Smile\GdprDump\Config\Validator\SchemaValidator;
use Smile\GdprDump\Util\Objects;
use stdClass;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Throwable;

final class DumpConfigLoader implements ConfigLoader
{
    /**
     * @var Resource[]
     */
    private array $resources = [];

    /**
     * @var string[]
     */
    private array $loadedFiles = [];
    private stdClass $container;

    /**
     * @param Parser[] $parsers
     */
    public function __construct(
        private iterable $parsers,
        private FileLocator $fileLocator,
        private SchemaValidator $validator,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function addResource(string $resource, Format $format): self
    {
        if ($format->isFile()) {
            $resource = $this->fileLocator->locate($resource);
        }

        $this->resources[] = new Resource($resource, $format, $this->getParser($format));

        return $this;
    }

    public function load(): object
    {
        $this->loadedFiles = [];
        $this->container = new stdClass();

        try {
            foreach ($this->resources as $resource) {
                $this->loadResource($resource);
            }

            $result = $this->validator->validate($this->container);
            if (!$result->isValid()) {
                throw new JsonSchemaValidationException($result->getMessages());
            }

            $this->eventDispatcher->dispatch(new ConfigLoadedEvent($this->container));
        } catch (Throwable $e) {
            if (!$e instanceof ConfigLoadException) {
                $e = new ConfigLoadException($e->getMessage(), $e);
            }

            throw $e;
        }

        return $this->container;
    }

    /**
     * Load the specified resource.
     */
    private function loadResource(Resource $resource)
    {
        $parsed = $resource->getParser()
            ->parse($resource->getInput());

        $this->eventDispatcher->dispatch(new ParseConfigEvent($parsed));

        // Recursively load parent config files
        if (isset($parsed->extends)) {
            $fileNames = (array) $parsed->extends;
            $currentDirectory = $this->getCurrentDirectory($resource);
            $this->loadParentResources($fileNames, $currentDirectory);
            unset($parsed->extends);
        }

        $this->eventDispatcher->dispatch(new MergeConfigEvent($parsed));
        Objects::merge($this->container, $parsed);
    }

    /**
     * Load parent config files.
     *
     * @param string[] $fileNames
     * @throws ConfigException
     */
    private function loadParentResources(array $fileNames, string $currentDirectory): void
    {
        foreach ($fileNames as $fileName) {
            $fileName = $this->fileLocator->locate($fileName, $currentDirectory);
            $resource = new Resource($fileName, Formats::YAML_FILE, $this->getParser(Formats::YAML_FILE));

            // Load the parent file if it was not already loaded
            if (!in_array($fileName, $this->loadedFiles, true)) {
                $this->loadResource($resource);
                $this->loadedFiles[] = $fileName;
            }
        }
    }

    /**
     * Get the loader than is able to process the specified resource.
     *
     * @throws ConfigLoadException
     */
    private function getParser(Format $format): Parser
    {
        foreach ($this->parsers as $parser) {
            if ($parser->supports($format)) {
                return $parser;
            }
        }

        throw new ParserNotFoundException(sprintf('No parser found for the resource type "%s".', $format->getName()));
    }

    /**
     * Get the current working directory (relative to the specified resource if it is a file).
     */
    private function getCurrentDirectory(Resource $resource): string
    {
        return $resource->getFormat()->isFile()
            ? dirname($resource->getInput())
            : getcwd();
    }
}
