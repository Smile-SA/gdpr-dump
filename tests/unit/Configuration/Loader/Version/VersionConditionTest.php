<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Configuration\Loader\Version;

use Smile\GdprDump\Configuration\Exception\ParseException;
use Smile\GdprDump\Configuration\Loader\Version\VersionCondition;
use Smile\GdprDump\Tests\Unit\TestCase;

final class VersionConditionTest extends TestCase
{
    /**
     * Test if the condition is properly parsed.
     */
    public function testConditionData(): void
    {
        // Test without space
        $condition = new VersionCondition('<=2.3.0');
        $this->assertSame('<=', $condition->getOperator());
        $this->assertSame('2.3.0', $condition->getVersion());
    }

    /**
     * Assert that an exception is thrown when the condition syntax is invalid.
     */
    public function testInvalidConditionSyntax(): void
    {
        $this->expectException(ParseException::class);
        new VersionCondition('not_valid');
    }
}
