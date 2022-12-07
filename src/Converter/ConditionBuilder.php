<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter;

use RuntimeException;
use TheSeer\Tokenizer\Token;
use TheSeer\Tokenizer\Tokenizer;

class ConditionBuilder
{
    private Tokenizer $tokenizer;

    /**
     * @var string[]
     */
    private array $statementBlacklist = ['<?php', '<?', '?>'];

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

        // Validate the condition
        $this->validateCondition($this->removeQuotedValues($condition));

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
        if (substr($condition, -1) !== ';') {
            $condition .= ';';
        }

        // Add return statement
        if (substr($condition, 0, 6) !== 'return') {
            $condition = 'return ' . $condition;
        }

        return $condition;
    }

    /**
     * Validate the condition.
     *
     * @throws RuntimeException
     */
    private function validateCondition(string $condition): void
    {
        // Prevent usage of "=" operator
        if (preg_match('/[^=!]=[^=]/', $condition)) {
            throw new RuntimeException('The operator "=" is not allowed in converter conditions.');
        }

        // Prevent usage of "$" character
        if (preg_match('/\$/', $condition)) {
            throw new RuntimeException('The character "$" is not allowed in converter conditions.');
        }

        // Prevent the use of some statements
        foreach ($this->statementBlacklist as $statement) {
            if (str_contains($condition, $statement)) {
                $message = sprintf('The statement "%s" is not allowed in converter conditions.', $statement);
                throw new RuntimeException($message);
            }
        }

        // Prevent the use of static functions
        if (preg_match('/::(\w+) *\(/', $condition)) {
            throw new RuntimeException('Static functions are not allowed in converter conditions.');
        }

        // Allow only specific functions
        if (preg_match_all('/(\w+) *\(/', $condition, $matches)) {
            foreach ($matches[1] as $function) {
                if (!$this->isFunctionAllowed($function)) {
                    $message = sprintf('The function "%s" is not allowed in converter conditions.', $function);
                    throw new RuntimeException($message);
                }
            }
        }
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
     * Remove quoted values from a variable,
     * e.g. `$s = 'value'` is converted to `$s = ''`.
     */
    private function removeQuotedValues(string $input): string
    {
        // Split the condition into PHP tokens
        $tokens = $this->tokenizer->parse('<?php ' . $input . ' ?>');
        $result = '';

        foreach ($tokens as $token) {
            // Remove quoted values
            $result .= $token->getName() === 'T_CONSTANT_ENCAPSED_STRING' ? "''" : $token->getValue();
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
     * Check if the token represents a variable.
     */
    private function isVariableToken(Token $token): bool
    {
        $name = $token->getName();

        return $name === 'T_OPEN_CURLY' || $name === 'T_CLOSE_CURLY' || $name === 'T_AT';
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
}
