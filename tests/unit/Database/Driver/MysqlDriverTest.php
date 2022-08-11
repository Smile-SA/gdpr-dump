<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Database\Driver;

use Smile\GdprDump\Database\Driver\MysqlDriver;
use Smile\GdprDump\Database\ParameterBag;
use Smile\GdprDump\Tests\Unit\TestCase;

class MysqlDriverTest extends TestCase
{
    /**
     * Test if the DSN is generated successfully.
     */
    public function testDsn(): void
    {
        $driver = $this->getMysqlDriver();
        $this->assertSame('mysql:host=localhost;dbname=mydatabase;charset=utf8mb4', $driver->getDsn());
    }

    /**
     * Get the MySQL driver.
     */
    private function getMysqlDriver(): MysqlDriver
    {
        $connectionParams = new ParameterBag([
            'host' => 'localhost',
            'dbname' => 'mydatabase',
            'user' => 'my_user',
            'password' => 'my_password',
            'charset' => 'utf8mb4',
        ]);

        return new MysqlDriver($connectionParams);
    }
}
