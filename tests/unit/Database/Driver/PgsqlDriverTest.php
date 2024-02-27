<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Database\Driver;

use Smile\GdprDump\Database\Driver\PostgresqlDriver;
use Smile\GdprDump\Database\ParameterBag;
use Smile\GdprDump\Tests\Unit\TestCase;

class PgsqlDriverTest extends TestCase
{
    /**
     * Test if the DSN is generated successfully.
     */
    public function testDsn(): void
    {
        $driver = $this->getPgsqlDriver();
        $this->assertSame('postgresql:host=localhost;dbname=mydatabase;charset=utf8', $driver->getDsn());
    }

    /**
     * Get the MySQL driver.
     */
    private function getPgsqlDriver(): PostgresqlDriver
    {
        $connectionParams = new ParameterBag([
            'driver' => 'pdo_pgsql',
            'host' => 'localhost',
            'dbname' => 'mydatabase',
            'user' => 'my_user',
            'password' => 'my_password',
            'charset' => 'utf8',
        ]);

        return new PostgresqlDriver($connectionParams);
    }
}
