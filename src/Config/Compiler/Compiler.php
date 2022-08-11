<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Compiler;

use Smile\GdprDump\Config\Compiler\Processor\ProcessorInterface;
use Smile\GdprDump\Config\ConfigInterface;

class Compiler
{
    /**
     * @param ProcessorInterface[] $processors
     */
    public function __construct(private array $processors = [])
    {
    }

    /**
     * Compile the configuration.
     *
     * @throws CompileException
     */
    public function compile(ConfigInterface $config): void
    {
        foreach ($this->processors as $processor) {
            $processor->process($config);
        }
    }
}
