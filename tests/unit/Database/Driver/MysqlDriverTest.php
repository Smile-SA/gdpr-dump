<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Database\Driver;

use Doctrine\DBAL\Connection;
use Smile\GdprDump\Database\Driver\MysqlDriver;
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
     *
     * @return MysqlDriver
     */
    private function getMysqlDriver(): MysqlDriver
    {
        $params = [
            'host' => 'localhost',
            'dbname' => 'mydatabase',
            'user' => 'my_user',
            'password' => 'my_password',
            'charset' => 'utf8mb4',
        ];

        $connectionMock = $this->createMock(Connection::class);
        $connectionMock->method('getParams')
            ->willReturn($params);

        return new MysqlDriver($connectionMock);
    }
}
