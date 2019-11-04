<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Proxy;

use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Proxy\Conditional;
use Smile\GdprDump\Tests\Framework\Mock\Converter\ConverterMock;
use Smile\GdprDump\Tests\Unit\TestCase;

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
     * Assert that an exception is thrown when the condition is not set.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testConditionNotSet()
    {
        $parameters = [
            'if_true_converter' => $this->createIfTrueConverter(),
        ];

        new Conditional($parameters);
    }

    /**
     * Assert that an exception is thrown when the converters are not set.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testConvertersNotSet()
    {
        $parameters = [
            'condition' => '{{id}} === 1',
        ];

        new Conditional($parameters);
    }

    /**
     * Assert that an exception is thrown when the condition contains a dollar symbol.
     *
     * @expectedException \RuntimeException
     */
    public function testErrorOnDollarSymbol()
    {
        $parameters = [
            'condition' => '$id === 1',
            'if_true_converter' => $this->createIfTrueConverter(),
        ];

        new Conditional($parameters);
    }

    /**
     * Assert that an exception is thrown when the condition contains a variable assignment.
     *
     * @expectedException \RuntimeException
     */
    public function testErrorOnAssignmentOperator()
    {
        $parameters = [
            'condition' => '{{id}} = 1',
            'if_true_converter' => $this->createIfTrueConverter(),
        ];

        new Conditional($parameters);
    }

    /**
     * Assert that an exception is thrown when the condition contains a PHP tag.
     *
     * @expectedException \RuntimeException
     */
    public function testErrorOnPhpTag()
    {
        $parameters = [
            'condition' => '<?php {{id}} === 1 ?>',
            'if_true_converter' => $this->createIfTrueConverter(),
        ];

        new Conditional($parameters);
    }

    /**
     * Assert that an exception is thrown when the condition contains a static function call
     *
     * @expectedException \RuntimeException
     */
    public function testErrorOnStaticFunction()
    {
        $parameters = [
            'condition' => 'ArrayHelper::getPath(\'id\') === 1',
            'if_true_converter' => $this->createIfTrueConverter(),
        ];

        new Conditional($parameters);
    }

    /**
     * Assert that an exception is thrown when the condition contains a forbidden function.
     *
     * @expectedException \RuntimeException
     */
    public function testErrorOnBlacklistedFunction()
    {
        $parameters = [
            'condition' => 'usleep(1000)',
            'if_true_converter' => $this->createIfTrueConverter(),
        ];

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
