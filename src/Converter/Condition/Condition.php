<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Condition;

use Smile\GdprDump\Dumper\DumpContext;

final class Condition
{
    /**
     * @phpstan-ignore property.onlyWritten (dump context is necessary for the evaluation of the condition)
     */
    public function __construct(private string $condition, private DumpContext $dumpContext)
    {
    }

    /**
     * Evaluate the condition.
     */
    public function evaluate(): bool
    {
        return (bool) eval($this->condition);
    }
}
