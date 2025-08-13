<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Util;

use Smile\GdprDump\Tests\Unit\TestCase;
use Smile\GdprDump\Util\Url;
use UnexpectedValueException;

final class UrlTest extends TestCase
{
    /**
     * Test the "parse" method.
     */
    public function testParseUrl(): void
    {
        $url = 'mysql://user:password@host:3306/db?charset=utf8mb4#fragment';
        $parsed = Url::parse($url);

        $this->assertArrayHasKey('scheme', $parsed);
        $this->assertArrayHasKey('user', $parsed);
        $this->assertArrayHasKey('pass', $parsed);
        $this->assertArrayHasKey('host', $parsed);
        $this->assertArrayHasKey('port', $parsed);
        $this->assertArrayHasKey('path', $parsed);
        $this->assertArrayHasKey('query', $parsed);
        $this->assertArrayHasKey('fragment', $parsed);

        $this->assertSame('mysql', $parsed['scheme']);
        $this->assertSame('user', $parsed['user']);
        $this->assertSame('password', $parsed['pass']);
        $this->assertSame('host', $parsed['host']);
        $this->assertSame(3306, $parsed['port']);
        $this->assertSame('/db', $parsed['path']);
        $this->assertSame('charset=utf8mb4', $parsed['query']);
        $this->assertSame('fragment', $parsed['fragment']);
    }

    /**
     * Assert that an exception is thrown when an invalid URL is parsed.
     */
    public function testInvalidUrl(): void
    {
        $this->expectException(UnexpectedValueException::class);
        Url::parse('invalid');
    }
}
