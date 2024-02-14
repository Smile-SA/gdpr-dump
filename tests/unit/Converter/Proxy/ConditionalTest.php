<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Proxy;

use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Tests\Framework\Mock\Converter\ConverterMock;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;
use stdClass;

class ConditionalTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testCondition(): void
    {
        $converter = $this->createConditionalConverter([
            'condition' => '{{id}} === @my_var',
            'if_true_converter' => $this->createIfTrueConverter(),
            'if_false_converter' => $this->createIfFalseConverter(),
        ]);

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
    public function testConvertersNotSet(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConditionalConverter(['condition' => '{{id}} === 1']);
    }

    /**
     * Assert that an exception is thrown when the parameter
     * "if_true_converter" is not an instance of ConverterInterface.
     */
    public function testIfTrueConverterNotValid(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConditionalConverter(['if_true_converter' => new stdClass()]);
    }

    /**
     * Assert that an exception is thrown when the parameter
     * "if_false_converter" is not an instance of ConverterInterface.
     */
    public function testIfFalseConverterNotValid(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConditionalConverter(['if_false_converter' => new stdClass()]);
    }

    /**
     * Assert that an exception is thrown when the parameter "condition" is not set.
     */
    public function testConditionNotSet(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConditionalConverter(['if_true_converter' => $this->createIfTrueConverter()]);
    }

    /**
     * Assert that an exception is thrown when the parameter "condition" is empty.
     */
    public function testEmptyCondition(): void
    {
        $this->expectException(ValidationException::class);
        $this->createConditionalConverter([
            'if_true_converter' => $this->createIfTrueConverter(),
            'condition' => '',
        ]);
    }

    /**
     * Create a test converter for conditions that evaluate to true.
     */
    private function createIfTrueConverter(): ConverterInterface
    {
        return $this->createConverter(ConverterMock::class, ['prefix' => 'success_']);
    }

    /**
     * Create a test converter for conditions that evaluate to false.
     */
    private function createIfFalseConverter(): ConverterInterface
    {
        return $this->createConverter(ConverterMock::class, ['prefix' => 'failure_']);
    }
}
