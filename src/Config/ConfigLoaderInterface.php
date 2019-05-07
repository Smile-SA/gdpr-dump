<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Config;

use Smile\Anonymizer\Config\Parser\ParseException;
use Smile\Anonymizer\Config\Validator\ValidationException;

interface ConfigLoaderInterface
{
    /**
     * Load a config file and merge its data to the config storage.
     *
     * @param string $fileName
     * @return $this
     * @throws ParseException
     * @throws ValidationException
     */
    public function loadFile(string $fileName): ConfigLoaderInterface;

    /**
     * Merge the specified data to the config storage.
     *
     * @param array $data
     * @return $this
     */
    public function loadData(array $data): ConfigLoaderInterface;

    /**
     * Load version-specific configuration.
     *
     * @return $this
     * @throws \RuntimeException
     */
    public function loadVersionData(): ConfigLoaderInterface;
}
