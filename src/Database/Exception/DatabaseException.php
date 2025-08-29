<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database\Exception;

use Doctrine\DBAL\Exception as DBALException;
use Throwable;

/**
 * Exception thrown when an error related to the database occurred.
 */
abstract class DatabaseException extends DBALException
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
