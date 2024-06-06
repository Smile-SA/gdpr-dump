<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Builder;

use Exception;
use Smile\GdprDump\Config\ConfigException;
use Smile\GdprDump\Config\Validator\ValidatorInterface;
use Symfony\Component\Yaml\Yaml;
use Throwable;

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
            throw $this->createException($fileName, $result->getMessages()[0]);
        }

        $this->checkWritable($fileName);

        // Create the configuration file
        try {
            $input = Yaml::dump($data, 4, 4, Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE | Yaml::DUMP_NULL_AS_TILDE);
            file_put_contents($fileName, $input);
        } catch (Exception $e) {
            throw $this->createException($fileName, $e->getMessage(), $e);
        }
    }

    /**
     * Check if the specified file can be created.
     *
     * @throws ConfigException
     */
    private function checkWritable(string $fileName): void
    {
        $directory = dirname($fileName);
        if ($directory === '') {
            throw $this->createException($fileName, 'path cannot be empty');
        }

        // Check if the directory exists
        if (!is_dir($directory)) {
            throw $this->createException($fileName, 'invalid directory');
        }

        if (!is_writable($directory)) {
            throw throw $this->createException($fileName, 'permission denied');
        }
    }

    /**
     * Create a config exception.
     */
    private function createException(string $fileName, string $reason, ?Throwable $previous = null): ConfigException
    {
        return new ConfigException(
            sprintf('Failed to create the config file "%s": %s.', $fileName, $reason),
            $previous
        );
    }
}
