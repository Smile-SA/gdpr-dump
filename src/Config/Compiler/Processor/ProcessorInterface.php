<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Compiler\Processor;

use Smile\GdprDump\Config\Compiler\CompileException;
use Smile\GdprDump\Config\ConfigInterface;

interface ProcessorInterface
{
    /**
     * Process the config data.
     *
     * @throws CompileException
     */
    public function process(ConfigInterface $config): void;
}
