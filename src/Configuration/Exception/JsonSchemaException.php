<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Exception;

use Throwable;

/**
 * Exception thrown when the configuration data is not valid against the json shema file.
 */
final class JsonSchemaException extends ConfigurationException
{
    /**
     * @param string[] $messages
     */
    public function __construct(private array $messages, ?Throwable $previous = null)
    {
        parent::__construct(implode(PHP_EOL, $messages), $previous);
    }

    /**
     * Get the validation errors.
     *
     * @return string[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
