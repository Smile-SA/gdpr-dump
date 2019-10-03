<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * Get the absolute path of the application.
     *
     * @return string
     */
    protected static function getBasePath(): string
    {
        return dirname(dirname(__DIR__));
    }

    /**
     * Get a resource file.
     *
     * @param string $fileName
     * @return string
     */
    protected static function getResource(string $fileName): string
    {
        return __DIR__ . '/Resources/' . $fileName;
    }
}
