<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Config;

use Smile\Anonymizer\Config\Parser\ParserInterface;
use Smile\Anonymizer\Config\Resolver\PathResolverInterface;
use Smile\Anonymizer\Config\Validator\ValidationException;
use Smile\Anonymizer\Config\Validator\ValidatorInterface;

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
    public function load(string $fileName): ConfigLoaderInterface
    {
        $fileName = $this->pathResolver->resolve($fileName);

        // Load the data
        $data = $this->parser->parse($fileName);

        // Recursively load parent config files
        if (isset($data['extends'])) {
            foreach ((array) $data['extends'] as $parentFile) {
                $this->load($parentFile);
            }
        }

        // Merge the loaded data with the current config
        $this->config->merge($data);

        return $this;
    }
}
