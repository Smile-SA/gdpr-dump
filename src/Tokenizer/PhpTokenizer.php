<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tokenizer;

class PhpTokenizer implements TokenizerInterface
{
    /**
     * Parse a PHP source into a list of tokens.
     *
     * @param string $value
     * @return Token[]
     */
    public function tokenize(string $value): array
    {
        $rawTokens = token_get_all($value);
        $tokens = [];

        foreach ($rawTokens as $rawToken) {
            $type = (int) (is_array($rawToken) ? $rawToken[0] : Token::T_UNKNOWN);
            $value = (string) (is_array($rawToken) ? $rawToken[1] : $rawToken);
            $tokens[] = new Token($type, $value);
        }

        return $tokens;
    }
}
