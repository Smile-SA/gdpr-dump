<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Exception;

/**
 * Exception thrown when invalid version data was provided (`version` and `if_version` parameters).
 */
final class InvalidVersionException extends ConfigLoadException
{
}
