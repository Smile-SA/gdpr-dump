<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Traits;

use Smile\GdprDump\Dumper\DumpContext;

trait HasDumpContext
{
    private DumpContext $dumpContext;

    public function setDumpContext(DumpContext $dumpContext): void
    {
        $this->dumpContext = $dumpContext;
    }
}
