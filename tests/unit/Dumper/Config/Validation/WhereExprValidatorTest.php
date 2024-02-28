<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Config\Validation;

use Smile\GdprDump\Dumper\Config\Validation\ValidationException;
use Smile\GdprDump\Dumper\Config\Validation\WhereExprValidator;
use Smile\GdprDump\Tests\Unit\TestCase;

class WhereExprValidatorTest extends TestCase
{
    /**
     * Assert that no exception is thrown when the expression is valid.
     */
    public function testValidExpression(): void
    {
        $this->expectNotToPerformAssertions();
        $queryValidator = new WhereExprValidator();
        $queryValidator->validate('email like "%@test.org" and created_at > date_sub(now(), interval 55 day)');
    }

    /**
     * Assert that no exception is thrown if the query includes a sub select.
     */
    public function testSubSelectIsAllowed(): void
    {
        $this->expectNotToPerformAssertions();
        $queryValidator = new WhereExprValidator();
        $queryValidator->validate('order_id in (select entity_id from order where status = "closed")');
    }

    /**
     * Assert that an exception is thrown when using a forbidden statement.
     */
    public function testForbiddenStatement(): void
    {
        $this->expectException(ValidationException::class);
        $queryValidator = new WhereExprValidator();
        $queryValidator->validate('drop database example');
    }

    /**
     * Assert that an exception is thrown if the query is terminated early.
     */
    public function testUnmatchedClosingBracket(): void
    {
        $this->expectException(ValidationException::class);
        $queryValidator = new WhereExprValidator();
        $queryValidator->validate('1); select * from customer where (1');
    }
}
