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

    /**
     * @inheritdoc
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * @inheritdoc
     */
    public function setValid(bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @inheritdoc
     */
    public function setMessages(array $messages): self
    {
        $this->messages = $messages;

        return $this;
    }
}
