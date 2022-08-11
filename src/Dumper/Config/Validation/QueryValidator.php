<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Config\Validation;

use TheSeer\Tokenizer\Tokenizer;

class QueryValidator
{
    private Tokenizer $tokenizer;

    /**
     * @var string[]
     */
    private array $statementBlacklist = [
        'grant', 'revoke', 'create', 'alter', 'drop', 'rename',
        'insert', 'update', 'delete', 'truncate', 'replace',
        'prepare', 'execute', 'lock', 'unlock', 'optimize', 'repair',
    ];

    /**
     * Create the query validator.
     */
    public function __construct()
    {
        $this->tokenizer = new Tokenizer();
    }

    /**
     * Validate that a SQL query is safe for execution.
     *
     * @throws ValidationException
     */
    public function validate(string $query): void
    {
        // Use a PHP tokenizer to split the query into tokens
        $tokens = $this->tokenizer->parse('<?php ' . strtolower($query) . '?>');

        foreach ($tokens as $token) {
            // If the token is a word, check if it contains a forbidden statement
            if ($token->getName() === 'T_STRING' && in_array($token->getValue(), $this->statementBlacklist, true)) {
                $message = 'The following query contains a forbidden keyword: "%s". '
                    . 'You may use "`%s`" to prevent this error.';
                throw new ValidationException(sprintf($message, $query, $token->getValue()));
            }
        }
    }
}
