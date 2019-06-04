<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Dumper\Sql\Driver;

use Smile\GdprDump\Dumper\Sql\Driver\DriverFactory;
use Smile\GdprDump\Dumper\Sql\Driver\Mysql;
use Smile\GdprDump\Tests\TestCase;

class DriverFactoryTest extends TestCase
{
    /**
     * Test the MySQL driver creation.
     */
    public function testMysqlDriver()
    {
        $driver = DriverFactory::create(DriverFactory::DRIVER_MYSQL);
        $this->assertInstanceOf(Mysql::class, $driver);
    }

    /**
     * Test if an exception is thrown when the driver name is invalid.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testInvalidDriverName()
    {
        DriverFactory::create('notExists');
    }
}
