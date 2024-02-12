<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Validator;

interface ValidationResultInterface
{
    /**
     * Check whether the validation status.
     */
    public function isValid(): bool;

    /**
     * Set the validation status.
     */
    public function setValid(bool $valid): self;

    /**
     * Get the validation messages.
     *
     * @return string[]
     */
    public function getMessages(): array;

    /**
     * Set the validation messages.
     *
     * @param string[] $messages
     */
    public function setMessages(array $messages): self;
}
