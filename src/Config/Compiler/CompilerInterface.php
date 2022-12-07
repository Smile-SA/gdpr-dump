<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Compiler;

use Smile\GdprDump\Config\ConfigInterface;

interface CompilerInterface
{
    /**
     * Compile the configuration.
     *
     * @throws CompileException
     */
    public function compile(ConfigInterface $config): void;
}
