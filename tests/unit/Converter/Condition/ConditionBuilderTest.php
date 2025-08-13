<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Condition;

use Smile\GdprDump\Converter\Condition\ConditionBuilder;
use Smile\GdprDump\Converter\Exception\InvalidConditionException;
use Smile\GdprDump\Tests\Unit\Converter\DumpContextAware;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;

final class ConditionBuilderTest extends TestCase implements DumpContextAware
{
    /**
     * Test the condition builder.
     */
    public function testConditionBuilder(): void
    {
        $builder = $this->createBuilder();

        $dumpContext = $this->getDumpContext();
        $dumpContext->currentRow['id'] = '10';
        $dumpContext->variables['my_var'] = '15';

        // Variables must be interpreted
        $condition = $builder->build('{{id}} === \'10\'');
        $this->assertTrue($condition->evaluate());

        $condition = $builder->build('@my_var === \'15\'');
        $this->assertTrue($condition->evaluate());

        // Variables must not be interpreted if they are encapsed by quotes
        $condition = $builder->build('\'{{id}}\' === \'10\'');
        $this->assertFalse($condition->evaluate());

        $condition = $builder->build('"@my_var" === \'15\'');
        $this->assertFalse($condition->evaluate());
    }

    /**
     * Assert that using an allowed function does not result in a thrown exception.
     */
    public function testAllowedFunctions(): void
    {
        $this->expectNotToPerformAssertions();
        $builder = $this->createBuilder();
        $builder->build('strpos({{email}}, "@acme.fr") !== false');
    }

    /**
     * Assert that an exception is thrown when an empty condition is specified.
     */
    public function testErrorOnEmptyCondition(): void
    {
        $builder = $this->createBuilder();
        $this->expectException(InvalidConditionException::class);
        $builder->build('');
    }

    /**
     * Assert that an exception is thrown when the condition contains a dollar symbol.
     */
    public function testErrorOnDollarSymbol(): void
    {
        $builder = $this->createBuilder();
        $this->expectException(InvalidConditionException::class);
        $builder->build('$1 = 1');
    }

    /**
     * Assert that an exception is thrown when the condition contains a variable assignment.
     */
    public function testErrorOnAssignmentOperator(): void
    {
        $builder = $this->createBuilder();
        $this->expectException(InvalidConditionException::class);
        $builder->build('{{id}} = 1');
    }

    /**
     * Assert that an exception is thrown when the condition contains a PHP tag.
     */
    public function testErrorOnPhpTag(): void
    {
        $builder = $this->createBuilder();
        $this->expectException(InvalidConditionException::class);
        $builder->build('<?php {{id}} === 1 ?>');
    }

    /**
     * Assert that an exception is thrown when the condition contains a forbidden function.
     */
    public function testErrorOnForbiddenFunction(): void
    {
        $builder = $this->createBuilder();
        $this->expectException(InvalidConditionException::class);
        $builder->build('usleep(1000)');
    }

    /**
     * Assert that an exception is thrown when the condition contains a forbidden function enclosed in quotes.
     */
    public function testErrorOnForbiddenStringFunction(): void
    {
        $builder = $this->createBuilder();
        $this->expectException(InvalidConditionException::class);
        $builder->build('\'usleep\'(1000)');
    }

    /**
     * Assert that an exception is thrown when the condition contains a static function call.
     */
    public function testErrorOnStaticFunction(): void
    {
        $builder = $this->createBuilder();
        $this->expectException(InvalidConditionException::class);
        $builder->build('Arrays::getPath(\'id\') === 1');
    }

    /**
     * Assert that an exception is thrown when the condition contains a static function call enclosed in quotes.
     */
    public function testErrorOnStringStaticFunction(): void
    {
        $builder = $this->createBuilder();
        $this->expectException(InvalidConditionException::class);
        $builder->build('\'Arrays\'::getPath(\'id\') === 1');
    }

    /**
     * Create a condition builder.
     */
    private function createBuilder(): ConditionBuilder
    {
        return new ConditionBuilder($this->getDumpContext());
    }
}
