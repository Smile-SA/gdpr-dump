<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config;

use Smile\GdprDump\Config\Parser\ParseException;
use Smile\GdprDump\Config\Parser\ParserInterface;
use Smile\GdprDump\Config\Processor\ProcessorInterface;
use Smile\GdprDump\Config\Resolver\FileNotFoundException;
use Smile\GdprDump\Config\Resolver\PathResolverInterface;

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
     * @var ProcessorInterface[]
     */
    private $processors;

    /**
     * @var PathResolverInterface
     */
    private $pathResolver;

    /**
     * @var string[]
     */
    private $parentTemplates = [];

    /**
     * @var string|null
     */
    private $currentDirectory;

    /**
     * @param ConfigInterface $config
     * @param ParserInterface $parser
     * @param ProcessorInterface[] $processors
     * @param PathResolverInterface $pathResolver
     */
    public function __construct(
        ConfigInterface $config,
        ParserInterface $parser,
        array $processors,
        PathResolverInterface $pathResolver
    ) {
        $this->config = $config;
        $this->parser = $parser;
        $this->processors = $processors;
        $this->pathResolver = $pathResolver;
    }

    /**
     * @inheritdoc
     */
    public function loadFile(string $fileName): ConfigLoaderInterface
    {
        // Resolve the path
        $fileName = $this->pathResolver->resolve($fileName, $this->currentDirectory);

        // Load the file contents
        $data = file_get_contents($fileName);
        if ($data === false) {
            throw new ParseException(sprintf('The file "%s" is not readable.', $fileName));
        }

        // Parse the file
        $data = $this->parser->parse(file_get_contents($fileName));

        // Make sure it was parsed into an array
        if (!is_array($data)) {
            throw new ParseException(sprintf('The file "%s" could not be parsed into an array.', $fileName));
        }

        // Parent config files must be loaded relatively to the path of the config file
        $this->currentDirectory = dirname($fileName);

        // Merge the data into the config
        $this->loadData($data);
        $this->currentDirectory = null;

        return $this;
    }

    /**
     * Load configuration data.
     *
     * @param array $data
     * @throws FileNotFoundException
     * @throws ParseException
     */
    private function loadData(array $data)
    {
        // Recursively load parent config files
        if (isset($data['extends'])) {
            foreach ((array) $data['extends'] as $parentFile) {
                // Load the parent template if it was not already loaded
                if (!in_array($parentFile, $this->parentTemplates, true)) {
                    $this->loadFile($parentFile);
                    $this->parentTemplates[] = $parentFile;
                }
            }

            unset($data['extends']);
        }

        // Run the processors on the config values
        $data = $this->process($data);

        $this->config->merge($data);
    }

    /**
     * Process the config data.
     *
     * @param array $data
     * @return array
     */
    private function process(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->process($value);
                continue;
            }

            foreach ($this->processors as $processor) {
                $data[$key] = $processor->process($value);
            }
        }

        return $data;
    }
}
