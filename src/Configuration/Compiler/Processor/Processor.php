<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Compiler\Processor;

use Smile\GdprDump\Configuration\Compiler\ProcessorType;
use Smile\GdprDump\Configuration\Exception\ConfigurationException;
use Smile\GdprDump\Configuration\Loader\Container;

interface Processor
{
    /**
     * Get the processor type (before or after validation).
     */
    public function getType(): ProcessorType;

    /**
     * Process the configuration.
     *
     * @throws ConfigurationException
     */
    public function process(Container $container): void;
}
