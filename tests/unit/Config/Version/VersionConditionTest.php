<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Version;

use Smile\GdprDump\Config\Version\VersionCondition;
use Smile\GdprDump\Tests\Unit\TestCase;

class VersionConditionTest extends TestCase
{
    /**
     * Test if the condition is properly parsed.
     */
    public function testConditionData()
    {
        // Test without space
        $condition = new VersionCondition('<=2.3.0');
        $this->assertSame('<=', $condition->getOperator());
        $this->assertSame('2.3.0', $condition->getVersion());
    }

    /**
     * Test the "match" method.
     */
    public function testConditionMatch()
    {
        $condition = new VersionCondition(('<2.3.0'));

        $this->assertTrue($condition->match('2.1'));
        $this->assertTrue($condition->match('2.1.0-alpha'));
        $this->assertTrue($condition->match('2.1.0'));
        $this->assertFalse($condition->match('2.3.0'));
    }

    /**
     * Test if an exception is thrown when the condition syntax is invalid.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidConditionSyntax()
    {
        new VersionCondition('notValid');
    }

    /**
     * Test if an exception is thrown when the condition does not contain at least 3 characters.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testErrorWithLessThanThreeCharacters()
    {
        new VersionCondition('<1');
    }
}
