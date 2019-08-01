<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Dumper\Sql\Doctrine;

use Doctrine\DBAL\Connection;
use Smile\GdprDump\Tests\DatabaseTestCase;
use Symfony\Component\Yaml\Yaml;

class ConnectionFactoryTest extends DatabaseTestCase
{
    /**
     * Test the connection factory.
     */
    public function testCreateConnection()
    {
        // Use the connection created by the test case
        $connection = $this->getConnection();
        $params = $this->getDatabaseParams();

        $this->assertInstanceOf(Connection::class, $connection);
        $this->assertSame($params['name'], $connection->getDatabase());
    }

    /**
     * Get the database params.
     *
     * @return array
     */
    private function getDatabaseParams(): array
    {
        $config = Yaml::parseFile(static::getTestConfigFile());

        return $config['database'];
    }
}
