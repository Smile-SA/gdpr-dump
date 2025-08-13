<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Config\Definition;

use Smile\GdprDump\Util\Collection;

/**
 * @extends Collection<ConverterConfig>
 */
final class ConverterConfigCollection extends Collection
{
    protected string $descriptor = 'converter';
}
