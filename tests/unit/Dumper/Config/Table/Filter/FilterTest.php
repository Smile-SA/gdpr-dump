<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Config\Table\Filter;

use Smile\GdprDump\Dumper\Config\Table\Filter\Filter;
use Smile\GdprDump\Tests\Unit\TestCase;
use UnexpectedValueException;

class FilterTest extends TestCase
{
    /**
     * Test the creation of a filter with a scalar value.
     */
    public function testScalarValue(): void
    {
        $filter = new Filter('column', Filter::OPERATOR_EQ, 1);

        $this->assertSame(Filter::OPERATOR_EQ, $filter->getOperator());
        $this->assertSame('column', $filter->getColumn());
        $this->assertSame(1, $filter->getValue());
    }

    /**
     * Test the creation of a filter with an array value.
     */
    public function testArrayValue(): void
    {
        $filter = new Filter('column', Filter::OPERATOR_IN, [1, 2]);

        $this->assertSame(Filter::OPERATOR_IN, $filter->getOperator());
        $this->assertSame('column', $filter->getColumn());
        $this->assertSame([1, 2], $filter->getValue());
    }

    /**
     * Assert that an exception is thrown when an invalid operator is used.
     */
    public function testInvalidOperator(): void
    {
        $this->expectException(UnexpectedValueException::class);
        new Filter('column', 'invalidOperator');
    }

    /**
     * Assert that an exception is thrown when the value is an array and the operator is neither "in" or "notIn".
     */
    public function testArrayValueWithIncompatibleOperator(): void
    {
        $this->expectException(UnexpectedValueException::class);
        new Filter('column', Filter::OPERATOR_EQ, [1]);
    }
}
