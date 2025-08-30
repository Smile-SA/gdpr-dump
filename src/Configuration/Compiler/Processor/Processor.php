<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Compiler\Processor;

use Smile\GdprDump\Configuration\Compiler\CompilerStep;
use Smile\GdprDump\Configuration\Exception\ConfigurationException;
use Smile\GdprDump\Configuration\Loader\Container;

interface Processor
{
    /**
     * Get the step during which the processor is executed.
     */
    public function getStep(): CompilerStep;

    /**
     * Process the configuration.
     *
     * @throws ConfigurationException
     */
    public function process(Container $container): void;
}
