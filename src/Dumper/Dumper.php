<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper;

use Smile\GdprDump\Configuration\Configuration;
use Smile\GdprDump\Dumper\Exception\DumpException;

interface Dumper
{
    /**
     * Create a dump according to the configuration.
     *
     * @throws DumpException
     */
    public function dump(Configuration $configuration, bool $dryRun = false): void;
}
