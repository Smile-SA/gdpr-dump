<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Exception;

/**
 * Exception thrown when the table name patterns could not be resolved (e.g. `log_*`).
 */
final class TableResolverException extends ConfigLoadException
{
}
