<?php
declare(strict_types=1);

namespace Smile\GdprDump\Config;

use Exception;
use Smile\GdprDump\Config\Parser\ParseException;
use Smile\GdprDump\Config\Parser\ParserInterface;
use Smile\GdprDump\Config\Resolver\PathResolverInterface;
use Smile\GdprDump\Config\Version\VersionCondition;

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
     * @param PathResolverInterface $pathResolver
     */
    public function __construct(
        ConfigInterface $config,
        ParserInterface $parser,
        PathResolverInterface $pathResolver
    ) {
        $this->config = $config;
        $this->parser = $parser;
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
     * @inheritdoc
     */
    public function loadVersionData(): ConfigLoaderInterface
    {
        $requiresVersion = (bool) $this->config->get('requires_version');
        $version = (string) $this->config->get('version');
        $versionsData = (array) $this->config->get('if_version');

        if ($version === '') {
            // Check if version is mandatory
            if ($requiresVersion) {
                // phpcs:ignore Generic.Files.LineLength.TooLong
                throw new ParseException('The application version must be specified in the configuration.');
            }
            return $this;
        }

        if (empty($versionsData)) {
            return $this;
        }

        // Merge version-specific data into the configuration
        foreach ($versionsData as $requirement => $versionData) {
            // Get the requirements as an array (e.g. '>2.0, <2.3' becomes an array of 2 elements)
            $conditions = array_map('trim', explode(',', $requirement));
            $matchVersion = true;

            // Check if all requirements match
            foreach ($conditions as $condition) {
                try {
                    $condition = new VersionCondition($condition);
                } catch (Exception $e) {
                    throw new ParseException($e->getMessage(), $e);
                }

                if (!$condition->match($version)) {
                    $matchVersion = false;
                    break;
                }
            }

            // If all requirements match, merge the version-specific data into the configuration
            if ($matchVersion) {
                $this->config->merge($versionData);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    private function loadData(array $data): ConfigLoaderInterface
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

        $this->config->merge($data);

        return $this;
    }
}
