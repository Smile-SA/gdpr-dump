<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Tests\Dumper\Sql\Driver;

use Smile\Anonymizer\Dumper\Sql\Config\DatabaseConfig;
use Smile\Anonymizer\Dumper\Sql\Driver\Mysql;
use Smile\Anonymizer\Tests\TestCase;

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
