<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Compiler\Processor\Version;

use Smile\GdprDump\Config\Compiler\Processor\Version\InvalidVersionException;
use Smile\GdprDump\Config\Compiler\Processor\Version\VersionCondition;
use Smile\GdprDump\Tests\Unit\TestCase;

class VersionConditionTest extends TestCase
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
        $this->expectException(InvalidVersionException::class);
        new VersionCondition('not_valid');
    }

    /**
     * Assert that an exception is thrown when the condition does not contain at least 3 characters.
     */
    public function testErrorWithLessThanThreeCharacters(): void
    {
        $this->expectException(InvalidVersionException::class);
        new VersionCondition('<1');
    }
}
