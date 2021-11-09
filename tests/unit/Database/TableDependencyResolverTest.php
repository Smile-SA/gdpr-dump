<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Database;

use Smile\GdprDump\Database\Metadata\Definition\Constraint\ForeignKey;
use Smile\GdprDump\Database\Metadata\MysqlMetadata;
use Smile\GdprDump\Database\TableDependencyResolver;
use Smile\GdprDump\Tests\Unit\TestCase;

class TableDependencyResolverTest extends TestCase
{
    /**
     * Test the "getDependencies" method.
     */
    public function testGetDependencies(): void
    {
        $fkMap = [
            ['stores', []],
            ['customers', [new ForeignKey('fk_stores', 'customers', ['store_id'], 'stores', ['store_id'])]],
            ['addresses', [new ForeignKey('fk_customers', 'addresses', ['customer_id'], 'customers', ['customer_id'])]],
        ];

        $dependencyResolver = $this->createTableDependencyResolver($fkMap);

        // Table "addresses" has no dependency
        $dependencies = $dependencyResolver->getDependencies(['addresses']);
        $this->assertEmpty($dependencies);

        // Table "customers" has 1 dependency (addresses)
        $dependencies = $dependencyResolver->getDependencies(['customers']);
        $this->assertCount(1, $dependencies);
        $this->assertHasAddressesDependency($dependencies);

        // Table "stores" has 2 dependencies (customers, addresses)
        $dependencies = $dependencyResolver->getDependencies(['stores']);
        $this->assertCount(2, $dependencies);
        $this->assertHasAddressesDependency($dependencies);
        $this->assertHasCustomersDependency($dependencies);

        // Passing all tables as function parameter returns all dependencies
        $dependencies = $dependencyResolver->getDependencies(['stores', 'customers', 'addresses']);
        $this->assertCount(2, $dependencies);
        $this->assertHasAddressesDependency($dependencies);
        $this->assertHasCustomersDependency($dependencies);
    }

    /**
     * Assert that the application does not get stuck in an infinite loop when a table depends on itself.
     */
    public function testCyclicDependencyOnSameTable(): void
    {
        $fkMap = [
            ['table', [new ForeignKey('fk', 'table', ['parent_id'], 'table', ['id'])]],
        ];

        $dependencyResolver = $this->createTableDependencyResolver($fkMap);

        // The foreign key must have been ignored by the resolver
        $dependencies = $dependencyResolver->getDependencies(['table']);
        $this->assertCount(1, $dependencies);
        $this->assertArrayHasKey('table', $dependencies);
        $this->assertCount(1, $dependencies['table']);
        $this->assertArrayHasKey('fk', $dependencies['table']);

        /** @var ForeignKey $foreignKey */
        $foreignKey = $dependencies['table']['fk'];
        $this->assertSame('fk', $foreignKey->getConstraintName());
    }

    /**
     * Assert that the application does not get stuck in an infinite loop when multiple tables depend on each other.
     */
    public function testCyclicDependencyOnTableSequence(): void
    {
        $fkMap = [
            ['table1', [new ForeignKey('fk1', 'table1', ['id'], 'table2', ['id'])]],
            ['table2', [new ForeignKey('fk2', 'table2', ['id'], 'table1', ['id'])]],
        ];

        $dependencyResolver = $this->createTableDependencyResolver($fkMap);

        $validateDependencies = function (array $dependencies): void {
            $this->assertCount(2, $dependencies);
            $this->assertArrayHasKey('table1', $dependencies);
            $this->assertArrayHasKey('table2', $dependencies);

            $this->assertCount(1, $dependencies['table1']);
            $this->assertArrayHasKey('fk1', $dependencies['table1']);

            /** @var ForeignKey $foreignKey */
            $foreignKey = $dependencies['table1']['fk1'];
            $this->assertSame('fk1', $foreignKey->getConstraintName());

            $this->assertCount(1, $dependencies['table2']);
            $this->assertArrayHasKey('fk2', $dependencies['table2']);

            /** @var ForeignKey $foreignKey */
            $foreignKey = $dependencies['table2']['fk2'];
            $this->assertSame('fk2', $foreignKey->getConstraintName());
        };

        $dependencies = $dependencyResolver->getDependencies(['table1', 'table2']);
        $validateDependencies($dependencies);

        // Same expected result when passing each table individually, because the tables depend on each other
        $dependencies = $dependencyResolver->getDependencies(['table1']);
        $validateDependencies($dependencies);

        $dependencies = $dependencyResolver->getDependencies(['table2']);
        $validateDependencies($dependencies);
    }

    /**
     * Assert that the dependency resolver can resolve a table with multiple foreign keys that reference the same table.
     */
    public function testForeignKeysWithSameReferencedTable(): void
    {
        $fkMap = [
            ['table1', []],
            [
                'table2',
                [
                    new ForeignKey('fk1', 'table2', ['column1'], 'table1', ['column1']),
                    new ForeignKey('fk2', 'table2', ['column2'], 'table1', ['column2']),
                ],
            ],
        ];

        $dependencyResolver = $this->createTableDependencyResolver($fkMap);
        $dependencies = $dependencyResolver->getDependencies(['table1', 'table2']);

        $this->assertCount(1, $dependencies);
        $this->assertArrayHasKey('table2', $dependencies);

        $this->assertCount(2, $dependencies['table2']);
        $this->assertArrayHasKey('fk1', $dependencies['table2']);
        $this->assertArrayHasKey('fk2', $dependencies['table2']);

        /** @var ForeignKey $foreignKey */
        $foreignKey = $dependencies['table2']['fk1'];
        $this->assertSame('fk1', $foreignKey->getConstraintName());

        /** @var ForeignKey $foreignKey */
        $foreignKey = $dependencies['table2']['fk2'];
        $this->assertSame('fk2', $foreignKey->getConstraintName());
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
     * Assert that the dependency array contains the foreign key used by the table "addresses".
     *
     * @param array $dependencies
     */
    private function assertHasAddressesDependency(array $dependencies): void
    {
        $this->assertArrayHasKey('addresses', $dependencies);
        $this->assertCount(1, $dependencies['addresses']);
        $this->assertArrayHasKey('fk_customers', $dependencies['addresses']);

        /** @var ForeignKey $foreignKey */
        $foreignKey = $dependencies['addresses']['fk_customers'];
        $this->assertSame('fk_customers', $foreignKey->getConstraintName());
    }

    /**
     * Assert that the dependency array contains the foreign key used by the table "customers".
     *
     * @param array $dependencies
     */
    private function assertHasCustomersDependency(array $dependencies): void
    {
        $this->assertArrayHasKey('customers', $dependencies);
        $this->assertCount(1, $dependencies['customers']);
        $this->assertArrayHasKey('fk_stores', $dependencies['customers']);

        /** @var ForeignKey $foreignKey */
        $foreignKey = $dependencies['customers']['fk_stores'];
        $this->assertSame('fk_stores', $foreignKey->getConstraintName());
    }
}
