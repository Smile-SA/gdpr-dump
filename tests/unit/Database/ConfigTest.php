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
    public function testParams(): void
    {
        $params = [
            'host' => 'mydb',
            'port' => '3306',
            'user' => 'myuser',
            'password' => 'mypassword',
            'dbname' => 'test',
            'charset' => 'utf8mb4',
            'driver' => 'pdo_mysql',
            'driverOptions' => [PDO::ATTR_TIMEOUT, 60],
        ];

        $config = new Config($params);
        $this->assertEquals($params, $config->getConnectionParams());
    }

    /**
     * Test the default values of the database config.
     */
    public function testDefaultValues(): void
    {
        $config = new Config(['dbname' => 'test']);

        $this->assertSame('pdo_mysql', $config->getConnectionParam('driver'));
        $this->assertSame('test', $config->getConnectionParam('dbname'));
        $this->assertSame('localhost', $config->getConnectionParam('host'));
        $this->assertNull($config->getConnectionParam('port'));
        $this->assertSame('root', $config->getConnectionParam('user'));
        $this->assertNull($config->getConnectionParam('driverOptions'));
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
