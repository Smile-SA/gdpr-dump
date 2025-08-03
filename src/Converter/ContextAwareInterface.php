<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter;

use Smile\GdprDump\Dumper\DumpContext;

interface ContextAwareInterface
{
    public function setDumpContext(DumpContext $dumpContext): void;
}
