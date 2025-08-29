<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Configuration\Validator;

use RuntimeException;
use Smile\GdprDump\Configuration\Exception\InvalidQueryException;
use Smile\GdprDump\Configuration\Validator\QueryValidator;
use Smile\GdprDump\Tests\Unit\TestCase;
use TheSeer\Tokenizer\Token;

final class QueryValidatorTest extends TestCase
{
    /**
     * Assert that no exceptio is thrown when the query is valid.
     */
    public function testAllowedStatement(): void
    {
        $this->expectNotToPerformAssertions();
        (new QueryValidator(['select']))->validate('select * from my_table');
    }

    /**
     * Assert that an exception is thrown when using a forbidden statement.
     */
    public function testForbiddenStatement(): void
    {
        $this->expectException(InvalidQueryException::class);
        (new QueryValidator(['set']))->validate('select * from my_table');
    }

    /**
     * Assert that the query validator is case-insensitive.
     */
    public function testIsCaseInsensitive(): void
    {
        $this->expectNotToPerformAssertions();
        (new QueryValidator(['select']))->validate('SELECT * from my_table');
    }

    /**
     * Assert that the validation callback parameter is working properly.
     */
    public function testValidationCallback(): void
    {
        $this->expectException(RuntimeException::class);
        (new QueryValidator(['select']))->validate('select * from my_table', function (Token $token): void {
            throw new RuntimeException($token->getValue());
        });
    }
}
