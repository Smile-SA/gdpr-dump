<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Condition;

use Smile\GdprDump\Converter\Condition\Condition;
use Smile\GdprDump\Tests\Unit\Converter\DumpContextAware;
use Smile\GdprDump\Tests\Unit\Converter\TestCase;

final class ConditionTest extends TestCase implements DumpContextAware
{
    /**
     * Test the condition evaluation.
     */
    public function testCondition(): void
    {
        $dumpContext = $this->getDumpContext();

        $condition = new Condition('return 1;', $dumpContext);
        $this->assertTrue($condition->evaluate());

        $condition = new Condition('return 0;', $dumpContext);
        $this->assertFalse($condition->evaluate());

        $dumpContext->currentRow['id'] = 10;
        $condition = new Condition('return $this->dumpContext->currentRow[\'id\'] === 10;', $dumpContext);
        $this->assertTrue($condition->evaluate());
    }
}
