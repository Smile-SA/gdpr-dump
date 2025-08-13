<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Exception;

use Smile\GdprDump\Config\Exception\ConfigLoadException;

/**
 * Exception thrown when an object could not be mapped from the configuration data.
 */
class MappingException extends ConfigLoadException
{
}
