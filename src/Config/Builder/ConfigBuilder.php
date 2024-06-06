<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Builder;

use Smile\GdprDump\Config\ConfigException;
use Smile\GdprDump\Config\Validator\ValidatorInterface;
use Symfony\Component\Yaml\Yaml;

class ConfigBuilder implements ConfigBuilderInterface
{
    public function __construct(private ValidatorInterface $validator)
    {
    }

    /**
     * @inheritdoc
     */
    public function build(string $fileName, array $data = []): void
    {
        // Validate the data
        $result = $this->validator->validate($data);
        if (!$result->isValid()) {
            throw new ConfigException(
                sprintf('Failed to create the config file "%s": %s.', $fileName, $result->getMessages()[0])
            );
        }

        // Create the YAML file
        if (!is_writable($fileName)) {
            throw new ConfigException(sprintf('Failed to create the config file "%s": permission denied.', $fileName));
        }

        $input = Yaml::dump($data, 4, 4, Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE | Yaml::DUMP_NULL_AS_TILDE);
        file_put_contents($fileName, $input);
    }
}
