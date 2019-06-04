<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Dumper\Sql\Doctrine;

use Doctrine\DBAL\Connection;
use Smile\GdprDump\Tests\DatabaseTestCase;

class ConnectionFactoryTest extends DatabaseTestCase
{
    /**
     * Test the connection factory.
     */
    public function testCreateConnection()
    {
        // Use the connection created by the test case
        $params = $this->getConnectionParams();
        $connection = $this->getConnection();

        $this->assertInstanceOf(Connection::class, $connection);
        $this->assertSame($params['dbname'], $connection->getDatabase());
    }
}
