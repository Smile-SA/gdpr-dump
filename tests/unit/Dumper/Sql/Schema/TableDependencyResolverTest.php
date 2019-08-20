<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Schema;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\MySqlSchemaManager;
use Smile\GdprDump\Dumper\Sql\Schema\TableDependencyResolver;
use Smile\GdprDump\Tests\Unit\TestCase;

class TableDependencyResolverTest extends TestCase
{
    /**
     * Test the "getTableDependencies" method.
     */
    public function testTableDependencies()
    {
        $dependencyResolver = $this->createTableDependencyResolver();

        // Table with no dependency
        $dependencies = $dependencyResolver->getTableDependencies('addresses');
        $this->assertEmpty($dependencies);

        // Table with a single dependency
        $dependencies = $dependencyResolver->getTableDependencies('customers');
        $this->assertCount(1, $dependencies);
        $this->assertHasTableDependency('addresses', 'customers', $dependencies);

        // Table with multiple dependencies
        $dependencies = $dependencyResolver->getTableDependencies('stores');
        $this->assertCount(2, $dependencies);
        $this->assertHasTableDependency('addresses', 'customers', $dependencies);
        $this->assertHasTableDependency('customers', 'stores', $dependencies);
    }

    /**
     * Test the "getTablesDependencies" method.
     */
    public function testTablesDependencies()
    {
        $dependencyResolver = $this->createTableDependencyResolver();

        $dependencies = $dependencyResolver->getTablesDependencies(['stores', 'customers', 'addresses']);
        $this->assertCount(2, $dependencies);
        $this->assertHasTableDependency('addresses', 'customers', $dependencies);
        $this->assertHasTableDependency('customers', 'stores', $dependencies);
    }

    /**
     * Create a table finder object.
     *
     * @return TableDependencyResolver
     */
    private function createTableDependencyResolver(): TableDependencyResolver
    {
        $tables = [
            'stores' => $this->createTable(),
            'customers' => $this->createTable([$this->createForeignKey('customers', 'stores')]),
            'addresses' => $this->createTable([$this->createForeignKey('addresses', 'customers')]),
        ];

        $schemaManagerMock = $this->createMock(MySqlSchemaManager::class);
        $schemaManagerMock->method('listTables')
            ->willReturn($tables);

        $connectionMock = $this->createMock(Connection::class);
        $connectionMock->method('getSchemaManager')
            ->willReturn($schemaManagerMock);

        return new TableDependencyResolver($connectionMock);
    }

    /**
     * Assert that a dependency exists between a child table and a parent table.
     *
     * @param string $localTableName
     * @param string $foreignTableName
     * @param array $dependencies
     */
    private function assertHasTableDependency(string $localTableName, string $foreignTableName, array $dependencies)
    {
        $this->assertArrayHasKey($localTableName, $dependencies);

        if (array_key_exists($localTableName, $dependencies)) {
            $this->assertCount(1, $dependencies[$localTableName]);
            $this->assertArrayHasKey($foreignTableName, $dependencies[$localTableName]);

            if (array_key_exists($foreignTableName, $dependencies[$localTableName][$foreignTableName])) {
                /** @var ForeignKeyConstraint $foreignKey */
                $foreignKey = $dependencies[$localTableName][$foreignTableName];
                $this->assertSame($foreignTableName, $foreignKey->getForeignTableName());
                $this->assertSame($localTableName, $foreignKey->getLocalTableName());
            }
        }
    }

    /**
     * Create a fake table object.
     *
     * @param array $foreignKeys
     * @return object
     */
    private function createTable(array $foreignKeys = [])
    {
        return new class ($foreignKeys) {
            /**
             * @var array
             */
            private $foreignKeys;

            /**
             * @param array $foreignKeys
             */
            public function __construct(array $foreignKeys = [])
            {
                $this->foreignKeys = $foreignKeys;
            }

            /**
             * Get the foreign keys.
             *
             * @return array
             */
            public function getForeignKeys(): array
            {
                return $this->foreignKeys;
            }
        };
    }

    /**
     * Create a fake foreign key object.
     *
     * @param string $localTableName
     * @param string $foreignTableName
     * @return object
     */
    private function createForeignKey(string $localTableName, string $foreignTableName)
    {
        return new class ($localTableName, $foreignTableName) {
            /**
             * @var string
             */
            private $localTableName;

            /**
             * @var string
             */
            private $foreignTableName;

            /**
             * @param string $localTableName
             * @param string $foreignTableName
             */
            public function __construct(string $localTableName, string $foreignTableName)
            {
                $this->localTableName = $localTableName;
                $this->foreignTableName = $foreignTableName;
            }

            /**
             * Get the local table name.
             *
             * @return string
             */
            public function getLocalTableName(): string
            {
                return $this->localTableName;
            }

            /**
             * Get the foreign table name.
             *
             * @return string
             */
            public function getForeignTableName(): string
            {
                return $this->foreignTableName;
            }
        };
    }
}
