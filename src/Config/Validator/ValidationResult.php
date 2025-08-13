<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Validator;

class ValidationResult
{
    /**
     * @param string[] $messages
     */
    public function __construct(private bool $valid, private array $messages)
    {

    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }
}
