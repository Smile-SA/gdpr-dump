<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Exception;

/**
 * Exception thrown by the object mapper when the configuration data is not valid against the json shema file.
 */
final class JsonSchemaValidationException extends MappingException
{
    /**
     * @param string[] $messages
     */
    public function __construct(private array $messages)
    {
        parent::__construct(implode(PHP_EOL, $messages));
    }

    /**
     * Get the error messages.
     *
     * @return string[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
