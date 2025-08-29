<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Exception;

/**
 * Exception thrown when a field has an invalid value (e.g. empty converter name).
 */
final class UnexpectedValueException extends ConfigurationException
{
}
