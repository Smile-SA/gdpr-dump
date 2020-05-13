<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter;

use RuntimeException;
use Smile\GdprDump\Converter\ConditionBuilder;
use Smile\GdprDump\Tests\Unit\TestCase;

class ConditionBuilderTest extends TestCase
{
    /**
     * Test the condition builder.
     */
    public function testConditionBuilder()
    {
        $builder = new ConditionBuilder();

        // Variables must be interpreted
        $condition = $builder->build('{{id}} === @my_var');
        $this->assertSame('return $context[\'row_data\'][\'id\'] === $context[\'vars\'][\'my_var\'];', $condition);

        // Variables must not be interpreted if they are encapsed by quotes
        $condition = $builder->build('\'{{2}}\' === "@2"');
        $this->assertSame('return \'{{2}}\' === "@2";', $condition);
    }

    /**
     * Assert that an exception is thrown when an empty condition is specified.
     */
    public function testErrorOnEmptyCondition()
    {
        $builder = new ConditionBuilder();
        $this->expectException(RuntimeException::class);
        $builder->build('');
    }

    /**
     * Assert that an exception is thrown when the condition contains a dollar symbol.
     */
    public function testErrorOnDollarSymbol()
    {
        $builder = new ConditionBuilder();
        $this->expectException(RuntimeException::class);
        $builder->build('$1 = 1');
    }

    /**
     * Assert that an exception is thrown when the condition contains a variable assignment.
     */
    public function testErrorOnAssignmentOperator()
    {
        $builder = new ConditionBuilder();
        $this->expectException(RuntimeException::class);
        $builder->build('{{id}} = 1');
    }

    /**
     * Assert that an exception is thrown when the condition contains a PHP tag.
     */
    public function testErrorOnPhpTag()
    {
        $builder = new ConditionBuilder();
        $this->expectException(RuntimeException::class);
        $builder->build('<?php {{id}} === 1 ?>');
    }

    /**
     * Assert that an exception is thrown when the condition contains a static function call.
     */
    public function testErrorOnStaticFunction()
    {
        $builder = new ConditionBuilder();
        $this->expectException(RuntimeException::class);
        $builder->build('ArrayHelper::getPath(\'id\') === 1');
    }

    /**
     * Assert that an exception is thrown when the condition contains a forbidden function.
     */
    public function testErrorOnBlacklistedFunction()
    {
        $builder = new ConditionBuilder();
        $this->expectException(RuntimeException::class);
        $builder->build('usleep(1000)');
    }
}
