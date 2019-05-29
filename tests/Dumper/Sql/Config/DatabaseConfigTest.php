<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Tests\Dumper\Sql\Config;

use Smile\Anonymizer\Dumper\Sql\Config\DatabaseConfig;
use Smile\Anonymizer\Tests\TestCase;

class DatabaseConfigTest extends TestCase
{
    /**
     * Test the default values of the database config.
     */
    public function testEmptyConfig()
    {
        $dbConfig = new DatabaseConfig(['name' => 'test']);

        $this->assertSame('pdo_mysql', $dbConfig->getDriver());
        $this->assertSame('localhost', $dbConfig->getHost());
        $this->assertSame('', $dbConfig->getPort());
        $this->assertSame('root', $dbConfig->getUser());
        $this->assertSame('', $dbConfig->getPassword());
        $this->assertSame('test', $dbConfig->getName());
        $this->assertEmpty($dbConfig->getPdoSettings());
    }

    /**
     * Test the database config with a configuration that contains all the possible parameters.
     */
    public function testFullConfig()
    {
        $data = [
            'driver' => 'pdo_sqlite',
            'host' => 'mydb',
            'port' => '3306',
            'user' => 'myuser',
            'password' => 'mypassword',
            'name' => 'test',
            'pdoSettings' => [\PDO::ATTR_TIMEOUT, 60],
        ];

        $dbConfig = new DatabaseConfig($data);

        $this->assertSame($data['driver'], $dbConfig->getDriver());
        $this->assertSame($data['host'], $dbConfig->getHost());
        $this->assertSame($data['port'], $dbConfig->getPort());
        $this->assertSame($data['user'], $dbConfig->getUser());
        $this->assertSame($data['password'], $dbConfig->getPassword());
        $this->assertSame($data['name'], $dbConfig->getName());
        $this->assertSame($data['pdoSettings'], $dbConfig->getPdoSettings());

        unset($data['pdoSettings']);
        $this->assertEmpty(array_diff($data, $dbConfig->toArray()));
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
