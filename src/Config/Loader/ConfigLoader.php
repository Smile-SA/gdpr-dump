<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Loader;

use Smile\GdprDump\Config\Config;
use Smile\GdprDump\Config\ConfigException;
use Smile\GdprDump\Config\ConfigInterface;
use Smile\GdprDump\Config\Event\LoadedEvent;
use Smile\GdprDump\Config\Event\LoadEvent;
use Smile\GdprDump\Config\Event\MergeEvent;
use Smile\GdprDump\Config\Event\ParseEvent;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

final class ConfigLoader implements ConfigLoaderInterface
{
    /**
     * @var string[]
     */
    private array $loadedFiles = [];

    public function __construct(
        private FileLocatorInterface $fileLocator,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function load(ConfigInterface $config, string ...$fileNames): void
    {
        $this->eventDispatcher->dispatch(new LoadEvent($config));

        foreach ($fileNames as $fileName) {
            $fileName = $this->fileLocator->locate($fileName);
            $this->loadFile($fileName, $config);
        }

        $this->eventDispatcher->dispatch(new LoadedEvent($config));
    }

    /**
     * Load a configuration file.
     *
     * @throws ConfigException
     */
    private function loadFile(string $fileName, ConfigInterface $config): void // TODO remove $config arg
    {
        $input = file_get_contents($fileName);
        if ($input === false) {
            throw new FileNotFoundException(sprintf('The file "%s" is not readable.', $fileName));
        }

        try {
            $data = Yaml::parse($input);
        } catch (Throwable $e) {
            throw new ParseException('Unable to parse the YAML input.', $e);
        }

        if (!is_array($data)) {
            throw new ParseException(sprintf('The file "%s" could not be parsed into an array.', $fileName));
        }

        $dataObject = new Config($data);
        $this->eventDispatcher->dispatch(new ParseEvent($dataObject));

        // Recursively load parent config files
        if (isset($data['extends'])) {
            $fileNames = (array) $data['extends'];
            $currentDirectory = dirname($fileName);
            $this->loadParentFiles($fileNames, $config, $currentDirectory);
            unset($data['extends']);
        }

        $this->eventDispatcher->dispatch(new MergeEvent($dataObject));
        $config->merge($dataObject->toArray());
    }

    /**
     * Load parent config files.
     *
     * @param string[] $fileNames
     * @throws ConfigException
     */
    private function loadParentFiles(array $fileNames, ConfigInterface $config, string $currentDirectory): void
    {
        foreach ($fileNames as $fileName) {
            $fileName = $this->fileLocator->locate($fileName, $currentDirectory);

            // Load the parent file if it was not already loaded
            if (!in_array($fileName, $this->loadedFiles, true)) {
                $this->loadFile($fileName, $config);
                $this->loadedFiles[] = $fileName;
            }
        }
    }
}
