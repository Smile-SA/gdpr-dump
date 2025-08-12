<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\Version;

use Smile\GdprDump\Config\Version\VersionMatcher;
use Smile\GdprDump\Tests\Unit\TestCase;

final class VersionMatcherTest extends TestCase
{
    /**
     * Test the "match" method with a single condition.
     */
    public function testMatchCondition(): void
    {
        $versionMatcher = new VersionMatcher();

        $this->assertTrue($versionMatcher->match('>=1.1.0', '1.1.0'));
        $this->assertTrue($versionMatcher->match('>=1.1.0', '2.0.0'));
        $this->assertFalse($versionMatcher->match('>=1.1.0', '1.0.18'));

        $this->assertTrue($versionMatcher->match('>=1.1', '1.1.0'));
        $this->assertTrue($versionMatcher->match('>=1.1', '2.0.0'));
        $this->assertFalse($versionMatcher->match('>=1.1', '1.0.18'));

        $this->assertTrue($versionMatcher->match('>=1.1', '1.1'));
        $this->assertTrue($versionMatcher->match('>=1.1', '2.0'));
        $this->assertFalse($versionMatcher->match('>=1.1', '1.0'));
    }

    /**
     * Test the "match" method with a condition range.
     */
    public function testMatchConditionRange(): void
    {
        $versionMatcher = new VersionMatcher();

        $this->assertTrue($versionMatcher->match('>=1.1.0 <2.0.0', '1.1.0'));
        $this->assertTrue($versionMatcher->match('>=1.1.0 <2.0.0', '1.18.18'));
        $this->assertFalse($versionMatcher->match('>=1.1.0 <2.0.0', '1.0.18'));
        $this->assertFalse($versionMatcher->match('>=1.1.0 <2.0.0', '2.0.0'));
    }
}
