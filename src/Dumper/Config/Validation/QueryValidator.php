<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Config\Validation;

use TheSeer\Tokenizer\Tokenizer;

class QueryValidator
{
    /**
     * @var Tokenizer
     */
    private $tokenizer;

    /**
     * @var string[]
     */
    private $statementBlacklist = [
        'grant', 'create', 'alter', 'drop', 'insert',
        'update', 'delete', 'truncate', 'replace',
        'prepare', 'execute',
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
     * @param string $query
     * @throws ValidationException
     */
    public function validate(string $query): void
    {
        // Use a PHP tokenizer to split the query into tokens
        $tokens = $this->tokenizer->parse('<?php ' . strtolower($query) . '?>');

        foreach ($tokens as $token) {
            // If the token is a word, check if it contains a forbidden statement
            if ($token->getName() === 'T_STRING' && in_array($token->getValue(), $this->statementBlacklist, true)) {
                throw new ValidationException(sprintf('This query contains forbidden keywords: "%s".', $query));
            }
        }
    }
}
