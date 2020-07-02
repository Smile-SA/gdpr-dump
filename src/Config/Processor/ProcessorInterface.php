<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Processor;

interface ProcessorInterface
{
    /**
     * Process a config value.
     *
     * @param mixed $value
     * @return mixed
     * @throws ProcessException
     */
    public function process($value);
}
