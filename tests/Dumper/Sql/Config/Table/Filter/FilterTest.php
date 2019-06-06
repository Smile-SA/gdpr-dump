<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Dumper\Sql\Config\Table\Filter;

use Smile\GdprDump\Dumper\Sql\Config\Table\Filter\Filter;
use Smile\GdprDump\Tests\TestCase;

class FilterTest extends TestCase
{
    /**
     * Test the creation of a filter with a scalar value.
     */
    public function testScalarValue()
    {
        $filter = new Filter('column', Filter::OPERATOR_EQ, 1);

        $this->assertSame(Filter::OPERATOR_EQ, $filter->getOperator());
        $this->assertSame('column', $filter->getColumn());
        $this->assertSame(1, $filter->getValue());
    }

    /**
     * Test the creation of a filter with an array value.
     */
    public function testArrayValue()
    {
        $filter = new Filter('column', Filter::OPERATOR_IN, [1, 2]);

        $this->assertSame(Filter::OPERATOR_IN, $filter->getOperator());
        $this->assertSame('column', $filter->getColumn());
        $this->assertSame([1, 2], $filter->getValue());
    }

    /**
     * Test if an exception is thrown when an invalid operator is used.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testInvalidOperator()
    {
        new Filter('column', 'invalidOperator');
    }

    /**
     * Test if an exception is thrown when the value is an array and the operator is neither "in" or "notIn".
     *
     * @expectedException \UnexpectedValueException
     */
    public function testInOperatorInvalidValue()
    {
        new Filter('column', Filter::OPERATOR_EQ, [1]);
    }
}
