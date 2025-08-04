<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper;

use Smile\GdprDump\Dumper\Listener\DataConverterListener;

/**
 * Stores the dump context data (e.g. row currently being dumped).
 *
 * This data is stored as arrays in public properties for performance reasons.
 * One of the functions that interacts with this data can potentially be called billions of times.
 *
 * phpcs:disable SlevomatCodingStandard.Classes.ForbiddenPublicProperty
 *
 * @see DataConverterListener::getHook()
 */
final class DumpContext
{
    /**
     * The table row that is currently being dumped (column name as key).
     *
     * @var array<string, ?scalar>
     */
    public array $currentRow = [];

    /**
     * Values of the current table row that were converted (column name as key).
     *
     * @var array<string, ?scalar>
     */
    public array $processedData = [];

    /**
     * The resolved SQL variables (variable name as key).
     *
     * @var array<string, string>
     */
    public array $variables = [];
}
