<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Config;

use Smile\Anonymizer\Config\Parser\ParseException;
use Smile\Anonymizer\Config\Validator\ValidationException;

interface ConfigLoaderInterface
{
    /**
     * Load a config file.
     *
     * @param string $fileName
     * @return $this
     * @throws ParseException
     * @throws ValidationException
     */
    public function load(string $fileName): ConfigLoaderInterface;
}
