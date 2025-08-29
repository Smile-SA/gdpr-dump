<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database\Exception;

/**
 * Exception thrown when trying to use an unsupported database driver.
 */
final class InvalidDriverException extends DatabaseException
{
}
