<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Sql\Driver;

use Doctrine\DBAL\Connection;
use Smile\GdprDump\Dumper\Sql\Driver\MysqlDriver;
use Smile\GdprDump\Tests\Unit\TestCase;

class MysqlDriverTest extends TestCase
{
    /**
     * Test if the DSN is generated successfully.
     */
    public function testDsn()
    {
        $driver = $this->getMysqlDriver();
        var_dump($driver->getDsn());

        $this->assertSame('mysql:host=localhost;dbname=mydatabase', $driver->getDsn());
    }

    /**
     * Get the MySQL driver.
     *
     * @return MysqlDriver
     */
    private function getMysqlDriver(): MysqlDriver
    {
        $connectionMock = $this->createMock(Connection::class);
        $connectionMock->method('getParams')
            ->willReturn(
                ['host' => 'localhost', 'dbname' => 'mydatabase', 'user' => 'my_user', 'password' => 'my_password']
            );

        return new MysqlDriver($connectionMock);
    }
}
