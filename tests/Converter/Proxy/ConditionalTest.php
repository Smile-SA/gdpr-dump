<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Converter\Proxy;

use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Proxy\Conditional;
use Smile\GdprDump\Tests\Converter\Dummy;
use Smile\GdprDump\Tests\TestCase;

class ConditionalTest extends TestCase
{
    /**
     * Test the converter.
     */
    public function testConverter()
    {
        $parameters = [
            'condition' => '{{id}} === 1',
            'if_true_converter' => $this->createIfTrueConverter(),
            'if_false_converter' => $this->createIfFalseConverter(),
        ];

        $converter = new Conditional($parameters);

        $value = $converter->convert('notAnonymized', ['id' => 1]);
        $this->assertSame('true_notAnonymized', $value);

        $value = $converter->convert('notAnonymized', ['id' => 2]);
        $this->assertSame('false_notAnonymized', $value);
    }

    /**
     * Test if an exception is thrown when the condition is not set.
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
     * Test if an exception is thrown when the converters are not set.
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
     * Test if an exception is thrown when the condition contains a dollar symbol.
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
     * Test if an exception is thrown when the condition contains a variable assignment.
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
     * Test if an exception is thrown when the condition contains a PHP tag.
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
     * Test if an exception is thrown when the condition contains a static function call
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
     * Test if an exception is thrown when the condition contains a forbidden function.
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
     * Create a dummy converter.
     *
     * @return Dummy
     */
    private function createIfTrueConverter(): ConverterInterface
    {
        return new Dummy(['prefix' => 'true_']);
    }

    /**
     * Create a dummy converter.
     *
     * @return Dummy
     */
    private function createIfFalseConverter(): ConverterInterface
    {
        return new Dummy(['prefix' => 'false_']);
    }
}
