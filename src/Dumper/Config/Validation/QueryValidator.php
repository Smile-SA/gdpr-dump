<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Config\Validation;

use TheSeer\Tokenizer\TokenCollection;
use TheSeer\Tokenizer\Tokenizer;

class QueryValidator
{
    private Tokenizer $tokenizer;

    /**
     * @var string[]
     */
    private array $statements = [
        'alter', 'analyse', 'backup', 'binlog', 'cache', 'change', 'close', 'commit', 'create',
        'deallocate', 'declare', 'delete', 'describe', 'drop', 'execute', 'explain', 'fetch', 'flush',
        'get', 'grant', 'help', 'install', 'kill', 'load', 'lock', 'open', 'optimize', 'prepare',
        'purge', 'rename', 'repair', 'reset', 'resignal', 'revoke', 'rollback', 'savepoint', 'select',
        'set', 'password', 'show', 'shutdown', 'signal', 'start', 'truncate', 'uninstall', 'unlock',
        'update', 'use', 'xa',
    ];

    /**
     * @var string[]
     */
    private array $allowedStatements;

    /**
     * @param string[] $allowedStatements
     */
    public function __construct(array $allowedStatements)
    {
        $this->tokenizer = new Tokenizer();

        // Better performance to check array keys
        $this->statements = array_flip($this->statements);
        $this->allowedStatements = array_flip($allowedStatements);
    }

    /**
     * Validate the query. An optional callback can be passed for additional validation.
     */
    public function validate(string $query, ?callable $callback = null): void
    {
        $tokens = $this->tokenize($query);

        foreach ($tokens as $token) {
            $name = $token->getName();
            $value = $token->getValue();

            if ($name === 'T_DEC' || $name === 'T_COMMENT') {
                throw new ValidationException(sprintf('Forbidden comment found in query "%s".', $query));
            }

            if ($name === 'T_STRING' && !$this->isStatementAllowed($value)) {
                throw new ValidationException(sprintf('Forbidden keyword "%s" found in query "%s".', $value, $query));
            }

            if ($callback !== null) {
                $callback($token);
            }
        }
    }

    /**
     * Tokenize the query.
     */
    private function tokenize(string $query): TokenCollection
    {
        return $this->tokenizer->parse('<?php ' . strtolower($query) . '?>');
    }

    /**
     * Check whether the statement is allowed.
     */
    private function isStatementAllowed(string $statement): bool
    {
        if (empty($this->allowedStatements)) {
            return !array_key_exists($statement, $this->statements);
        }

        return array_key_exists($statement, $this->allowedStatements)
            || !array_key_exists($statement, $this->statements);
    }
}
