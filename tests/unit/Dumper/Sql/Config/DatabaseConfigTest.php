<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Sql\Config;

use PDO;
use Smile\GdprDump\Dumper\Sql\Config\DatabaseConfig;
use Smile\GdprDump\Tests\Unit\TestCase;

class DatabaseConfigTest extends TestCase
{
    /**
     * Test the getter methods.
     */
    public function testGetters()
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

        $config = new DatabaseConfig($params);

        $this->assertSame($params['driver'], $config->getDriver());
        $this->assertSame($params['driver_options'], $config->getDriverOptions());

        unset($params['driver']);
        unset($params['driver_options']);
        $this->assertEmpty(array_diff($params, $config->getConnectionParams()));
    }

    /**
     * Test the default values of the database config.
     */
    public function testDefaultValues()
    {
        $config = new DatabaseConfig(['name' => 'test']);

        $this->assertSame('pdo_mysql', $config->getDriver());
        $this->assertSame('test', $config->getConnectionParam('name'));
        $this->assertSame('localhost', $config->getConnectionParam('host'));
        $this->assertNull($config->getConnectionParam('port'));
        $this->assertSame('root', $config->getConnectionParam('user'));
        $this->assertEmpty($config->getDriverOptions());
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
}
