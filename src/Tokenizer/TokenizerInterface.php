<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tokenizer;

interface TokenizerInterface
{
    /**
     * Parse the specified value into a list of tokens.
     *
     * @param string $value
     * @return Token[]
     */
    public function tokenize(string $value): array;
}
