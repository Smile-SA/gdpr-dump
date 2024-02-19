<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Loader;

use Exception;
use Smile\GdprDump\Config\ConfigException;
use Smile\GdprDump\Config\ConfigInterface;
use Symfony\Component\Yaml\Yaml;

class ConfigLoader implements ConfigLoaderInterface
{
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
    public function load(string $fileName, ConfigInterface $config): void
    {
        $fileName = $this->fileLocator->locate($fileName);
        $this->loadFile($fileName, $config);
    }

    /**
     * Load a configuration file.
     *
     * @throws ConfigException
     */
    private function loadFile(string $fileName, ConfigInterface $config): void
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
            $this->loadParentFiles($fileNames, $config, $currentDirectory);
            unset($data['extends']);
        }

        $config->merge($data);
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
            if (!in_array($fileName, $this->loadedTemplates, true)) {
                $this->loadFile($fileName, $config);
                $this->loadedTemplates[] = $fileName;
            }
        }
    }
}
