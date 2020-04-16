<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter;

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
     *
     * @expectedException \RuntimeException
     */
    public function testErrorOnEmptyCondition()
    {
        $builder = new ConditionBuilder();
        $builder->build('');
    }

    /**
     * Assert that an exception is thrown when the condition contains a dollar symbol.
     *
     * @expectedException \RuntimeException
     */
    public function testErrorOnDollarSymbol()
    {
        $builder = new ConditionBuilder();
        $builder->build('$1 = 1');
    }

    /**
     * Assert that an exception is thrown when the condition contains a variable assignment.
     *
     * @expectedException \RuntimeException
     */
    public function testErrorOnAssignmentOperator()
    {
        $builder = new ConditionBuilder();
        $builder->build('{{id}} = 1');
    }

    /**
     * Assert that an exception is thrown when the condition contains a PHP tag.
     *
     * @expectedException \RuntimeException
     */
    public function testErrorOnPhpTag()
    {
        $builder = new ConditionBuilder();
        $builder->build('<?php {{id}} === 1 ?>');
    }

    /**
     * Assert that an exception is thrown when the condition contains a static function call
     *
     * @expectedException \RuntimeException
     */
    public function testErrorOnStaticFunction()
    {
        $builder = new ConditionBuilder();
        $builder->build('ArrayHelper::getPath(\'id\') === 1');
    }

    /**
     * Assert that an exception is thrown when the condition contains a forbidden function.
     *
     * @expectedException \RuntimeException
     */
    public function testErrorOnBlacklistedFunction()
    {
        $builder = new ConditionBuilder();
        $builder->build('usleep(1000)');
    }
}
