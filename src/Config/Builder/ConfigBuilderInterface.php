<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Builder;

use Smile\GdprDump\Config\ConfigException;

interface ConfigBuilderInterface
{
    /**
     * Create a configuration file with the specified contents.
     *
     * Throws ConfigException if the file is not writable or the data is not valid.
     *
     * @throws ConfigException
     */
    public function build(string $fileName, array $data = []): void;
}
