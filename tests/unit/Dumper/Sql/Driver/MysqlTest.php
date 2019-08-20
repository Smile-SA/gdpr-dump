<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Sql\Driver;

use Smile\GdprDump\Dumper\Sql\Config\DatabaseConfig;
use Smile\GdprDump\Dumper\Sql\Driver\Mysql;
use Smile\GdprDump\Tests\Unit\TestCase;

class MysqlTest extends TestCase
{
    /**
     * Test if the DSN is generated successfully.
     */
    public function testDsn()
    {
        $config = new DatabaseConfig([
            'host' => 'localhost',
            'user' => 'myuser',
            'password' => 'mypassword',
            'name' => 'mydatabase',
        ]);

        $driver = new Mysql();
        $this->assertSame('mysql:host=localhost;dbname=mydatabase', $driver->getDsn($config));
    }
}
