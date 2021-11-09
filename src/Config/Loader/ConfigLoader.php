<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Loader;

use Smile\GdprDump\Config\ConfigException;
use Smile\GdprDump\Config\ConfigInterface;
use Smile\GdprDump\Config\Parser\ParseException;
use Smile\GdprDump\Config\Parser\ParserInterface;

class ConfigLoader implements ConfigLoaderInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var ParserInterface
     */
    private $parser;

    /**
     * @var FileLocatorInterface
     */
    private $fileLocator;

    /**
     * @var string[]
     */
    private $loadedTemplates = [];

    /**
     * @param ConfigInterface $config
     * @param ParserInterface $parser
     * @param FileLocatorInterface $fileLocator
     */
    public function __construct(
        ConfigInterface $config,
        ParserInterface $parser,
        FileLocatorInterface $fileLocator
    ) {
        $this->config = $config;
        $this->parser = $parser;
        $this->fileLocator = $fileLocator;
    }

    /**
     * @inheritdoc
     */
    public function load(string $fileName): void
    {
        $fileName = $this->fileLocator->locate($fileName);
        $this->loadFile($fileName);
    }

    /**
     * Load a configuration file.
     *
     * @param string $fileName
     * @throws ConfigException
     */
    private function loadFile(string $fileName): void
    {
        // Load the file contents
        $input = file_get_contents($fileName);
        if ($input === false) {
            throw new ParseException(sprintf('The file "%s" is not readable.', $fileName));
        }

        // Parse the file
        $data = $this->parser->parse($input);

        // Make sure it was parsed into an array
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
     * @param string $currentDirectory
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
