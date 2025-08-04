<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Get the absolute path of the application.
     */
    protected static function getBasePath(): string
    {
        return dirname(__DIR__, 2);
    }

    /**
     * Get a resource file.
     */
    protected static function getResource(string $fileName): string
    {
        return __DIR__ . '/Resources/' . $fileName;
    }

    /**
     * Assert that two arrays have the same key/value pairs. Sort order of keys can be different.
     */
    protected function assertSameKeyValuePairs(array $expected, array $actual): void
    {
        ksort($expected);
        ksort($actual);

        $this->assertSame($expected, $actual);
    }
}
