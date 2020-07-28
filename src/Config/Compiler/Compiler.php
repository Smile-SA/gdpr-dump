<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Compiler;

use Smile\GdprDump\Config\Compiler\Processor\ProcessorInterface;
use Smile\GdprDump\Config\ConfigInterface;

class Compiler
{
    /**
     * @var ProcessorInterface[]
     */
    private array $processors;

    /**
     * @param ProcessorInterface[] $processors
     */
    public function __construct(array $processors = [])
    {
        $this->processors = $processors;
    }

    /**
     * Compile the configuration.
     *
     * @param ConfigInterface $config
     * @throws CompileException
     */
    public function compile(ConfigInterface $config): void
    {
        foreach ($this->processors as $processor) {
            $processor->process($config);
        }
    }
}
