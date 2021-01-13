<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Database;

use PDO;
use Smile\GdprDump\Database\Config;
use Smile\GdprDump\Tests\Unit\TestCase;
use UnexpectedValueException;

class ConfigTest extends TestCase
{
    /**
     * Test the getter methods.
     */
    public function testGetters(): void
    {
        $params = [
            'host' => 'mydb',
            'port' => '3306',
            'user' => 'myuser',
            'password' => 'mypassword',
            'name' => 'test',
            'charset' => 'utf8mb4',
            'driver' => 'pdo_mysql',
            'driver_options' => [PDO::ATTR_TIMEOUT, 60],
        ];

        $config = new Config($params);

        $this->assertSame($params['driver'], $config->getDriver());
        $this->assertSame($params['driver_options'], $config->getDriverOptions());

        unset($params['driver']);
        unset($params['driver_options']);
        $this->assertEmpty(array_diff($params, $config->getConnectionParams()));
    }

    /**
     * Test the default values of the database config.
     */
    public function testDefaultValues(): void
    {
        $config = new Config(['name' => 'test']);

        $this->assertSame('pdo_mysql', $config->getDriver());
        $this->assertSame('test', $config->getConnectionParam('name'));
        $this->assertSame('localhost', $config->getConnectionParam('host'));
        $this->assertNull($config->getConnectionParam('port'));
        $this->assertSame('root', $config->getConnectionParam('user'));
        $this->assertEmpty($config->getDriverOptions());
    }

    /**
     * Assert that an exception is thrown when the database name is missing.
     */
    public function testMissingDatabaseName(): void
    {
        $this->expectException(UnexpectedValueException::class);
        new Config([]);
    }
}
