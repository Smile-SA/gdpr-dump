<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Dumper\Sql\Config\Table\Filter;

use Smile\GdprDump\Dumper\Sql\Config\Table\Filter\Filter;
use Smile\GdprDump\Tests\TestCase;

class FilterTest extends TestCase
{
    /**
     * Test the creation of a filter.
     */
    public function testFilter()
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
     * Test if an exception is thrown when the value is not an array with the "in" operator.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testInOperatorInvalidValue()
    {
        new Filter('column', Filter::OPERATOR_IN, 'value');
    }

    /**
     * Test if an exception is thrown when the value is not an array with the "not in" operator.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testNotInOperatorInvalidValue()
    {
        new Filter('column', Filter::OPERATOR_NOT_IN, 'value');
    }
}
