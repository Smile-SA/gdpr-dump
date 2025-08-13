<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Loader\Processor;

use stdClass;

interface Processor
{
    /**
     * Process the configuration.
     */
    public function process(stdClass $configuration): void;
}
