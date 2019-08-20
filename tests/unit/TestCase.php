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
     * Get a file resource.
     *
     * @param string $fileName
     * @return string
     */
    protected static function getResource(string $fileName): string
    {
        return __DIR__ . '/Resources/' . $fileName;
    }

    /**
     * Get the config file used for the tests.
     *
     * @return string
     */
    protected static function getTestConfigFile(): string
    {
        return static::getResource('config/templates/test.yaml');
    }
}
