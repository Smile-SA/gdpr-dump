<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Database;

use PDO;
use Smile\GdprDump\Database\ParameterBag;
use Smile\GdprDump\Tests\Unit\TestCase;
use UnexpectedValueException;

class ParameterBagTest extends TestCase
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

        $bag = new ParameterBag($params);
        $this->assertEquals($params, $bag->all());
    }

    /**
     * Test the default values of the database config.
     */
    public function testDefaultValues(): void
    {
        $bag = new ParameterBag(['dbname' => 'test', 'driver' => 'pdo_mysql']);
        $this->assertSame('test', $bag->get('dbname'));
        $this->assertSame('localhost', $bag->get('host'));
        $this->assertNull($bag->get('port'));
        $this->assertSame('root', $bag->get('user'));
        $this->assertNull($bag->get('driverOptions'));
    }

    /**
     * Test the default values of the database config.
     */
    public function testDriverIsMissing(): void
    {
        $this->expectException(UnexpectedValueException::class);
         new ParameterBag(['dbname' => 'test']);
    }

    /**
     * Assert that an exception is thrown when the database name is missing.
     */
    public function testMissingDatabaseName(): void
    {
        $this->expectException(UnexpectedValueException::class);
        new ParameterBag([]);
    }
}
