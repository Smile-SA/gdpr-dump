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
     * Test the "getTablesDependencies" method.
     */
    public function testGetDependencies(): void
    {
        $fkMap = [
            ['stores', []],
            [
                'customers',
                [
                    new ForeignKey('fk_c1', 'customers', ['store_id'], 'stores', ['store_id']),
                    new ForeignKey('fk_c2', 'customers', ['main_billing_address_id'], 'addresses', ['address_id']),
                    new ForeignKey('fk_c3', 'customers', ['main_shipping_address_id'], 'addresses', ['address_id']),
                ]
            ],
            ['addresses', [new ForeignKey('fk_a1', 'addresses', ['customer_id'], 'customers', ['customer_id'])]],
        ];

        /**
         * Expected array:
         * - addresses
         *     - fk_a1: FK object
         * - customers:
         *     - fk_c1: FK object
         *     - fk_c2: FK object
         *     - fk_c3: FK object
         */
        $dependencyResolver = $this->createTableDependencyResolver($fkMap);
        $dependencies = $dependencyResolver->getDependencies(['stores', 'customers', 'addresses']);
        $this->assertCount(2, $dependencies);
        $this->assertArrayHasKey('customers', $dependencies);
        $this->assertArrayHasKey('addresses', $dependencies);
        $this->assertCount(3, $dependencies['customers']);
        $this->assertArrayHasKey('fk_c1', $dependencies['customers']);
        $this->assertArrayHasKey('fk_c2', $dependencies['customers']);
        $this->assertArrayHasKey('fk_c3', $dependencies['customers']);
        $this->assertCount(1, $dependencies['addresses']);
        $this->assertArrayHasKey('fk_a1', $dependencies['addresses']);

        /**
         * Expected array:
         * - addresses
         *     - fk_a1: FK object
         * - customers:
         *     - fk_c1: FK object
         *     - fk_c2: FK object
         *     - fk_c3: FK object
         */
        $dependencies = $dependencyResolver->getDependencies(['addresses']);
        $this->assertCount(2, $dependencies);
        $this->assertArrayHasKey('customers', $dependencies);
        $this->assertArrayHasKey('addresses', $dependencies);
        $this->assertCount(2, $dependencies['customers']);
        $this->assertArrayHasKey('fk_c2', $dependencies['customers']);
        $this->assertArrayHasKey('fk_c3', $dependencies['customers']);
        $this->assertCount(1, $dependencies['addresses']);
        $this->assertArrayHasKey('fk_a1', $dependencies['addresses']);
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

        $dependencies = $dependencyResolver->getDependencies(['table1', 'table2']);
        $this->assertCount(2, $dependencies);
        $this->assertArrayHasKey('table1', $dependencies);
        $this->assertArrayHasKey('table2', $dependencies);
        $this->assertCount(1, $dependencies['table1']);
        $this->assertArrayHasKey('fk1', $dependencies['table1']);
        $this->assertCount(1, $dependencies['table2']);
        $this->assertArrayHasKey('fk2', $dependencies['table2']);
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
}
