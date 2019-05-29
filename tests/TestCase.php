<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Get a file resource.
     *
     * @param string $fileName
     * @return string
     */
    protected function getResource(string $fileName): string
    {
        return APP_ROOT . '/tests/Resources/' . $fileName;
    }
}
