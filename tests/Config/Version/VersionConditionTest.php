<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Tests\Config\Version;

use PHPUnit\Framework\TestCase;
use Smile\Anonymizer\Config\Version\VersionCondition;

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
     * Test if an exception is thrown when the condition is invalid.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testConditionError()
    {
        new VersionCondition('notValid');
    }
}
