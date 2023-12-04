<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Loader;

use Exception;
use Smile\GdprDump\Config\ConfigException;
use Smile\GdprDump\Config\ConfigInterface;
use Symfony\Component\Yaml\Yaml;

class ConfigLoader implements ConfigLoaderInterface
{
    private ConfigInterface $config;

    /**
     * @var string[]
     */
    private array $loadedTemplates = [];

    public function __construct(private FileLocatorInterface $fileLocator)
    {
    }

    /**
     * @inheritdoc
     */
    public function load(string $fileName): void
    {
        if (!isset($this->config)) {
            throw new ConfigException('The configuration object must be set.');
        }

        $fileName = $this->fileLocator->locate($fileName);
        $this->loadFile($fileName);
    }

    /**
     * @inheritdoc
     */
    public function setConfig(ConfigInterface $config): self
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Load a configuration file.
     *
     * @throws ConfigException
     */
    private function loadFile(string $fileName): void
    {
        $input = file_get_contents($fileName);
        if ($input === false) {
            throw new FileNotFoundException(sprintf('The file "%s" is not readable.', $fileName));
        }

        try {
            $data = Yaml::parse($input);
        } catch (Exception $e) {
            throw new ParseException('Unable to parse the YAML input.', $e);
        }

        if (!is_array($data)) {
            throw new ParseException(sprintf('The file "%s" could not be parsed into an array.', $fileName));
        }

        // Recursively load parent config files
        if (isset($data['extends'])) {
            $fileNames = (array) $data['extends'];
            $currentDirectory = dirname($fileName);
            $this->loadParentFiles($fileNames, $currentDirectory);
            unset($data['extends']);
        }

        $this->config->merge($data);
    }

    /**
     * Load parent config files.
     *
     * @param string[] $fileNames
     * @throws ConfigException
     */
    private function loadParentFiles(array $fileNames, string $currentDirectory): void
    {
        foreach ($fileNames as $fileName) {
            $fileName = $this->fileLocator->locate($fileName, $currentDirectory);

            // Load the parent file if it was not already loaded
            if (!in_array($fileName, $this->loadedTemplates, true)) {
                $this->loadFile($fileName);
                $this->loadedTemplates[] = $fileName;
            }
        }
    }
}
