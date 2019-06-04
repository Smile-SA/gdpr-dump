<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Dumper\Sql\Config;

use Smile\GdprDump\Dumper\Sql\Config\DatabaseConfig;
use Smile\GdprDump\Dumper\Sql\Driver\DriverFactory;
use Smile\GdprDump\Tests\TestCase;

class DatabaseConfigTest extends TestCase
{
    /**
     * Test the getter methods.
     */
    public function testGetters()
    {
        $params = [
            'driver' => DriverFactory::DRIVER_MYSQL,
            'host' => 'mydb',
            'port' => '3306',
            'user' => 'myuser',
            'password' => 'mypassword',
            'name' => 'test',
            'pdo_settings' => [\PDO::ATTR_TIMEOUT, 60],
        ];

        $config = new DatabaseConfig($params);

        $this->assertSame($params['driver'], $config->getDriver());
        $this->assertSame($params['host'], $config->getHost());
        $this->assertSame($params['port'], $config->getPort());
        $this->assertSame($params['user'], $config->getUser());
        $this->assertSame($params['password'], $config->getPassword());
        $this->assertSame($params['name'], $config->getDatabaseName());
        $this->assertSame($params['pdo_settings'], $config->getPdoSettings());

        unset($params['pdo_settings']);
        $this->assertEmpty(array_diff($params, $config->getParams()));
    }

    /**
     * Test the default values of the database config.
     */
    public function testDefaultValues()
    {
        $config = new DatabaseConfig(['name' => 'test']);

        $this->assertSame('pdo_mysql', $config->getDriver());
        $this->assertSame('localhost', $config->getHost());
        $this->assertSame('', $config->getPort());
        $this->assertSame('root', $config->getUser());
        $this->assertSame('', $config->getPassword());
        $this->assertSame('test', $config->getDatabaseName());
        $this->assertEmpty($config->getPdoSettings());
    }

    /**
     * Test if an exception is thrown when the database name is missing.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testMissingDatabaseName()
    {
        new DatabaseConfig([]);
    }

    /**
     * Test if an exception is thrown when an invalid parameter is used.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testInvalidParameterName()
    {
        new DatabaseConfig(['name' => 'test', 'notExists' => true]);
    }
}
