<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Proxy;

use InvalidArgumentException;
use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Proxy\Conditional;
use Smile\GdprDump\Tests\Framework\Mock\Converter\ConverterMock;
use Smile\GdprDump\Tests\Unit\TestCase;
use UnexpectedValueException;

class ConditionalTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testCondition()
    {
        $parameters = [
            'condition' => '{{id}} === @my_var',
            'if_true_converter' => $this->createIfTrueConverter(),
            'if_false_converter' => $this->createIfFalseConverter(),
        ];

        $converter = new Conditional($parameters);

        $value = $converter->convert('value', ['row_data' => ['id' => 1], 'vars' => ['my_var' => 1]]);
        $this->assertSame('success_value', $value);

        $value = $converter->convert('value', ['row_data' => ['id' => 1], 'vars' => ['my_var' => 2]]);
        $this->assertSame('failure_value', $value);

        $value = $converter->convert('value', ['row_data' => ['id' => 2], 'vars' => ['my_var' => 1]]);
        $this->assertSame('failure_value', $value);
    }

    /**
     * Assert that an exception is thrown when the converters are not set.
     */
    public function testConvertersNotSet()
    {
        $parameters = [
            'condition' => '{{id}} === 1',
        ];

        $this->expectException(InvalidArgumentException::class);
        new Conditional($parameters);
    }

    /**
     * Assert that an exception is thrown when the parameter "condition" is not set.
     */
    public function testConditionNotSet()
    {
        $parameters = [
            'if_true_converter' => $this->createIfTrueConverter(),
        ];

        $this->expectException(InvalidArgumentException::class);
        new Conditional($parameters);
    }

    /**
     * Assert that an exception is thrown when the parameter "condition" is empty.
     */
    public function testEmptyCondition()
    {
        $parameters = [
            'if_true_converter' => $this->createIfTrueConverter(),
            'condition' => '',
        ];

        $this->expectException(UnexpectedValueException::class);
        new Conditional($parameters);
    }

    /**
     * Create a test converter for conditions that evaluate to true.
     *
     * @return ConverterMock
     */
    private function createIfTrueConverter(): ConverterInterface
    {
        return new ConverterMock(['prefix' => 'success_']);
    }

    /**
     * Create a test converter for conditions that evaluate to false.
     *
     * @return ConverterMock
     */
    private function createIfFalseConverter(): ConverterInterface
    {
        return new ConverterMock(['prefix' => 'failure_']);
    }
}
