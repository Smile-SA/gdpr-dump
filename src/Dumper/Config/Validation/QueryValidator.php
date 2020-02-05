<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Config\Validation;

use Smile\GdprDump\Tokenizer\PhpTokenizer;

class QueryValidator
{
    /**
     * @var PhpTokenizer
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
        $this->tokenizer = new PhpTokenizer();
    }

    /**
     * Validate that a SQL query is safe for execution.
     *
     * @param string $query
     * @throws ValidationException
     */
    public function validate(string $query)
    {
        // Use a PHP tokenizer to split the query into tokens
        $tokens = $this->tokenizer->tokenize('<?php ' . strtolower($query) . '?>');

        foreach ($tokens as $token) {
            // If the token is a word, check if it contains a forbidden statement
            if ($token->getType() === T_STRING && in_array($token->getValue(), $this->statementBlacklist, true)) {
                throw new ValidationException(sprintf('This query contains forbidden keywords: "%s".', $query));
            }
        }
    }
}
