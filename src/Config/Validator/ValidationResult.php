<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Validator;

final class ValidationResult implements ValidationResultInterface
{
    private bool $valid = false;

    /**
     * @var string[]
     */
    private array $messages = [];

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function setValid(bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function setMessages(array $messages): self
    {
        $this->messages = $messages;

        return $this;
    }
}
