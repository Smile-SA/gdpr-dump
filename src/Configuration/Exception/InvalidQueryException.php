<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Exception;

/**
 * Exception thrown when a SQL query contains disallowed statements.
 */
final class InvalidQueryException extends ConfigurationException
{
}
