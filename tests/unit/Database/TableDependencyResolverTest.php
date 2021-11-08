<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Database;

use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Smile\GdprDump\Database\Metadata\Definition\Constraint\ForeignKey;
use Smile\GdprDump\Database\Metadata\MysqlMetadata;
use Smile\GdprDump\Database\TableDependencyResolver;
use Smile\GdprDump\Tests\Unit\TestCase;

class TableDependencyResolverTest extends TestCase
{
    /**
     * Test the "getTableDependencies" method.
     */
    public function testTableDependencies(): void
    {
        $dependencyResolver = $this->createTableDependencyResolver($this->getStoreFkMap());

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
    public function testTablesDependencies(): void
    {
        $dependencyResolver = $this->createTableDependencyResolver($this->getStoreFkMap());

        $dependencies = $dependencyResolver->getTablesDependencies(['stores', 'customers', 'addresses']);
        $this->assertCount(2, $dependencies);
        $this->assertHasTableDependency('addresses', 'customers', $dependencies);
        $this->assertHasTableDependency('customers', 'stores', $dependencies);
    }

    /**
     * Assert that cyclic dependencies do not result in an infinite loop.
     */
    public function testCyclicDependencies(): void
    {
        $dependencyResolver = $this->createTableDependencyResolver($this->getCyclicFkMap());

        $dependencies = $dependencyResolver->getTablesDependencies(['table1', 'table2']);
        $this->assertCount(2, $dependencies);
        $this->assertHasTableDependency('table1', 'table2', $dependencies);
        $this->assertHasTableDependency('table2', 'table1', $dependencies);
    }

    /**
     * Assert that a dependency exists between a child table and a parent table.
     *
     * @param string $localTableName
     * @param string $foreignTableName
     * @param array $dependencies
     */
    private function assertHasTableDependency(
        string $localTableName,
        string $foreignTableName,
        array $dependencies
    ): void {
        $this->assertArrayHasKey($localTableName, $dependencies);
        $this->assertCount(1, $dependencies[$localTableName]);
        $this->assertArrayHasKey($foreignTableName, $dependencies[$localTableName]);

        /** @var ForeignKeyConstraint $foreignKey */
        $foreignKey = $dependencies[$localTableName][$foreignTableName];
        $this->assertSame($foreignTableName, $foreignKey->getForeignTableName());
        $this->assertSame($localTableName, $foreignKey->getLocalTableName());
    }

    /**
     * Create a table dependency resolver object.
     *
     * @param array $foreignKeyMap
     * @return TableDependencyResolver
     */
    private function createTableDependencyResolver(array $foreignKeyMap): TableDependencyResolver
    {
        $metadataMock = $this->createMock(MysqlMetadata::class);

        // Mock the "getTableNames" method
        $tableNames = array_column($foreignKeyMap, 0);
        $metadataMock->method('getTableNames')
            ->willReturn($tableNames);

        // Mock the "getForeignKeys" method
        $metadataMock->method('getForeignKeys')
            ->willReturnMap($foreignKeyMap);

        /** @var MysqlMetadata $metadataMock */
        return new TableDependencyResolver($metadataMock);
    }

    /**
     * Returns a foreign key map that simulates a store.
     *
     * @return array[]
     */
    private function getStoreFkMap(): array
    {
        return [
            ['stores', []],
            ['customers', [new ForeignKey('fk1', 'customers', ['store_id'], 'stores', ['store_id'])]],
            ['addresses', [new ForeignKey('fk2', 'addresses', ['customer_id'], 'customers', ['customer_id'])]],
        ];
    }

    /**
     * Returns a foreign key map that simulates a cyclic dependency.
     *
     * @return array[]
     */
    private function getCyclicFkMap(): array
    {
        return [
            ['table1', [new ForeignKey('fk1', 'table1', ['version_id'], 'table2', ['version_id'])]],
            ['table2', [new ForeignKey('fk2', 'table2', ['version_id'], 'table1', ['version_id'])]],
        ];
    }
}
