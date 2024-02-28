<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Config\Validation;

use TheSeer\Tokenizer\Token;

class WhereExprValidator
{
    private QueryValidator $queryValidator;

    public function __construct()
    {
        $this->queryValidator = new QueryValidator(['select']);
    }

    /**
     * Validate the where expression.
     */
    public function validate(string $expr): void
    {
        $openedBrackets = 0;

        $this->queryValidator->validate($expr, function (Token $token) use ($expr, &$openedBrackets): void {
            // Disallow using a closing bracket if there is no matching opening bracket -> prevents SQL injection
            if ($token->getName() === 'T_OPEN_BRACKET') {
                ++$openedBrackets;
                return;
            }

            if ($token->getName() === 'T_CLOSE_BRACKET') {
                if ($openedBrackets === 0) {
                    throw new ValidationException(sprintf('Unmatched closing bracket found in query "%s".', $expr));
                }

                --$openedBrackets;
            }
        });
    }
}
