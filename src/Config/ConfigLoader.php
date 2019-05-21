<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Config;

use Smile\Anonymizer\Config\Parser\ParserInterface;
use Smile\Anonymizer\Config\Resolver\PathResolverInterface;
use Smile\Anonymizer\Config\Version\VersionCondition;

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
        $fileName = $this->pathResolver->resolve($fileName);

        // Parse the data
        $data = $this->parser->parse($fileName);

        // Merge the data into the config
        $this->loadData($data);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function loadData(array $data): ConfigLoaderInterface
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

    /**
     * @inheritdoc
     */
    public function loadVersionData(): ConfigLoaderInterface
    {
        $requiresVersion = (bool) $this->config->get('requiresVersion');
        $version = (string) $this->config->get('version');
        $versionsData = (array) $this->config->get('if_version');

        if ($version === '') {
            // Check if version is mandatory
            if ($requiresVersion) {
                // phpcs:disable Generic.Files.LineLength.TooLong
                throw new \RuntimeException('The application version must be specified in the configuration, or with the "--additional-config" option.');
                // phpcs:enable
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
                $condition = new VersionCondition($condition);

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
}
