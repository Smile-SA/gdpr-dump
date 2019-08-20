<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Schema;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\MySqlSchemaManager;
use Smile\GdprDump\Dumper\Sql\Schema\TableFinder;
use Smile\GdprDump\Tests\Unit\TestCase;

class TableFinderTest extends TestCase
{
    /**
     * Test if a table is found by name.
     */
    public function testFindByName()
    {
        $tableFinder = $this->createTableFinder();

        // Exact match
        $this->assertSame(['table1'], $tableFinder->findByName('table1'));

        // Pattern
        $this->assertSame(['table1', 'table2', 'table3'], $tableFinder->findByName('table*'));
    }

    /**
     * Create a table finder object.
     *
     * @return TableFinder
     */
    private function createTableFinder(): TableFinder
    {
        $schemaManagerMock = $this->createMock(MySqlSchemaManager::class);
        $schemaManagerMock->method('listTableNames')
            ->willReturn(['table1', 'table2', 'table3']);

        $connectionMock = $this->createMock(Connection::class);
        $connectionMock->method('getSchemaManager')
            ->willReturn($schemaManagerMock);

        return new TableFinder($connectionMock);
    }
}
