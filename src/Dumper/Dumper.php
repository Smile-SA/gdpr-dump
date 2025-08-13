<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper;

use Smile\GdprDump\Config\DumperConfig;

interface Dumper
{
    /**
     * Create a dump according to the configuration.
     */
    public function dump(DumperConfig $config, bool $dryRun = false): void;
}
