<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Processor;

interface ProcessorInterface
{
    /**
     * Process a config value.
     *
     * @param mixed $value
     */
    public function process($value);
}
