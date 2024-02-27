<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter;

use RuntimeException;
use TheSeer\Tokenizer\Token;
use TheSeer\Tokenizer\TokenCollection;
use TheSeer\Tokenizer\Tokenizer;

class ConditionBuilder
{
    private Tokenizer $tokenizer;

    /**
     * @var string[]
     */
    private array $functionWhitelist = [
        'addslashes', 'array_*', 'chr', 'date', 'empty', 'explode', 'htmlentities', 'htmlspecialchars',
        'implode', 'in_array', 'is_*', 'isset', 'lcfirst', 'ltrim', 'mb_*', 'number_format', 'ord',
        'preg_*', 'rtrim', 'sprintf', 'str_*', 'strchr', 'strcmp', 'strcoll', 'strcspn', 'stripcslashes',
        'stripos', 'strip_tags', 'stripslashes', 'stristr', 'strlen', 'strnatcasecmp', 'strnatcmp',
        'strncasecmp', 'strncmp', 'strpbrk', 'strpos', 'strrchr', 'strrev', 'strripos', 'strrpos',
        'strspn', 'strstr', 'strtok', 'strtolower', 'strtoupper', 'strtr', 'substr', 'substr_*',
        'time', 'trim', 'ucfirst', 'ucwords', 'vsprintf', 'wordwrap',
    ];

    public function __construct()
    {
        $this->tokenizer = new Tokenizer();
    }

    /**
     * Build the condition.
     *
     * @throws RuntimeException
     */
    public function build(string $condition): string
    {
        if ($condition === '') {
            throw new RuntimeException('The condition must not be empty.');
        }

        // Sanitize the condition
        $condition = $this->sanitizeCondition($condition);

        // Parse the condition and return the result
        return $this->parseCondition($condition);
    }

    /**
     * Sanitize the condition.
     */
    private function sanitizeCondition(string $condition): string
    {
        // Remove line breaks
        $condition = (string) preg_replace('/[\r\n]+/', ' ', $condition);

        // Add instruction separator
        if (!str_ends_with($condition, ';')) {
            $condition .= ';';
        }

        // Add return statement
        if (!str_starts_with($condition, 'return')) {
            $condition = 'return ' . $condition;
        }

        return $condition;
    }

    /**
     * Parse the tokens that represent the condition, and return the parsed condition.
     */
    private function parseCondition(string $condition): string
    {
        // Split the condition into PHP tokens
        $tokens = $this->tokenizer->parse('<?php ' . $condition . ' ?>');
        $tokenCount = count($tokens);
        $result = '';
        $index = -1;

        foreach ($tokens as $token) {
            ++$index;

            $this->validateToken($token, $tokens, $index, $tokenCount);

            // Skip characters representing a variable
            if ($this->isVariableToken($token)) {
                continue;
            }

            // Replace SQL column names by their values in the condition
            if (
                $token->getName() === 'T_STRING'
                && $index >= 2
                && $index <= $tokenCount - 3
                && $tokens[$index - 1]->getName() === 'T_OPEN_CURLY'
                && $tokens[$index - 2]->getName() === 'T_OPEN_CURLY'
                && $tokens[$index + 1]->getName() === 'T_CLOSE_CURLY'
                && $tokens[$index + 2]->getName() === 'T_CLOSE_CURLY'
            ) {
                $result .= "\$context['row_data']['{$token->getValue()}']";
                continue;
            }

            // Replace SQL variable names by their values in the condition
            if ($token->getName() === 'T_STRING' && $index >= 1 && $tokens[$index - 1]->getName() === 'T_AT') {
                $result .= "\$context['vars']['{$token->getValue()}']";
                continue;
            }

            $result .= $token->getValue();
        }

        return $this->removePhpTags($result);
    }

    /**
     * Remove opening and closing PHP tags from a string.
     */
    private function removePhpTags(string $input): string
    {
        return rtrim(ltrim($input, '<?php '), ' ?>');
    }

    /**
     * Assert that the token is allowed.
     *
     * @throws RuntimeException
     */
    private function validateToken(Token $token, TokenCollection $tokens, int $index, int $tokenCount): void
    {
        if ($index > 0 && $token->getName() === 'T_OPEN_TAG') {
            throw new RuntimeException('PHP opening tags are not allowed in converter conditions.');
        }

        if ($index < $tokenCount - 1 && $token->getName() === 'T_CLOSE_TAG') {
            throw new RuntimeException('PHP closing tags are not allowed in converter conditions.');
        }

        if ($token->getName() === 'T_EQUAL') {
            throw new RuntimeException('The operator "=" is not allowed in converter conditions.');
        }

        if ($token->getName() === 'T_VARIABLE') {
            throw new RuntimeException('The character "$" is not allowed in converter conditions.');
        }

        if ($token->getName() === 'T_OPEN_BRACKET') {
            // Search for forbidden functions and static calls
            $previousTokenPos = $this->getPreviousTokenPos($tokens, $index);
            if ($previousTokenPos !== null) {
                $previousToken = $tokens[$previousTokenPos];

                if (
                    $previousToken->getName() === 'T_STRING' // `die()`
                    || $previousToken->getName() === 'T_CONSTANT_ENCAPSED_STRING' // `'die'()`
                    || $previousToken->getName() === 'T_VARIABLE' // `$func()`
                ) {
                    // Function detected, check if it is allowed
                    $function = $previousToken->getValue();
                    if (!$this->isFunctionAllowed($function)) {
                        $message = sprintf('The function "%s" is not allowed in converter conditions.', $function);
                        throw new RuntimeException($message);
                    }

                    // If the previous token is `::`, then it's a static call
                    $previousTokenPos = $this->getPreviousTokenPos($tokens, $previousTokenPos);
                    if ($previousTokenPos !== null) {
                        $previousToken = $tokens[$previousTokenPos];
                        if ($previousToken->getName() === 'T_DOUBLE_COLON') {
                            throw new RuntimeException('Static functions are not allowed in converter conditions.');
                        }
                    }
                }
            }
        }
    }

    /**
     * Check whether the specified PHP function is allowed.
     */
    private function isFunctionAllowed(string $function): bool
    {
        $allowed = false;

        foreach ($this->functionWhitelist as $pattern) {
            if (fnmatch($pattern, $function)) {
                $allowed = true;
                break;
            }
        }

        return $allowed;
    }

    /**
     * Search for the previous non-whitespace token.
     */
    private function getPreviousTokenPos(TokenCollection $tokens, int $currentIndex): ?int
    {
        --$currentIndex;
        while ($currentIndex > 0) {
            if ($tokens[$currentIndex]->getName() !== 'T_WHITESPACE') {
                return $currentIndex;
            }
            --$currentIndex;
        }

        return null;
    }

    /**
     * Check if the token represents a variable.
     */
    private function isVariableToken(Token $token): bool
    {
        $name = $token->getName();

        return $name === 'T_OPEN_CURLY' || $name === 'T_CLOSE_CURLY' || $name === 'T_AT';
    }
}
