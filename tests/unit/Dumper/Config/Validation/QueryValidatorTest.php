<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Config\Validation;

use RuntimeException;
use Smile\GdprDump\Dumper\Config\Validation\QueryValidator;
use Smile\GdprDump\Dumper\Config\Validation\ValidationException;
use Smile\GdprDump\Tests\Unit\TestCase;
use TheSeer\Tokenizer\Token;

class QueryValidatorTest extends TestCase
{
    /**
     * Assert that no exceptio is thrown when the query is valid.
     */
    public function testAllowedStatement(): void
    {
        $this->expectNotToPerformAssertions();
        $queryValidator = new QueryValidator(['select']);
        $queryValidator->validate('select * from my_table');
    }

    /**
     * Assert that an exception is thrown when using a forbidden statement.
     */
    public function testForbiddenStatement(): void
    {
        $this->expectException(ValidationException::class);
        $queryValidator = new QueryValidator(['set']);
        $queryValidator->validate('select * from my_table');
    }

    /**
     * Assert that the query validator is case insensitive.
     */
    public function testIsCaseInsensitive(): void
    {
        $this->expectNotToPerformAssertions();
        $queryValidator = new QueryValidator(['select']);
        $queryValidator->validate('SELECT * from my_table');
    }

    /**
     * Assert that the validation callback parameter is working properly.
     */
    public function testValidationCallback(): void
    {
        $this->expectException(RuntimeException::class);
        $queryValidator = new QueryValidator(['select']);
        $queryValidator->validate('select * from my_table', function (Token $token): void {
            throw new RuntimeException($token->getValue());
        });
    }
}
