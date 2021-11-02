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

        //Tables with a dependency on each other A -> B; B -> A
        $dependencies = $dependencyResolver->getTableDependencies('media');
        $this->assertCount(2, $dependencies);
        $this->assertHasTableDependency('media', 'users', $dependencies);
        $this->assertHasTableDependency('users', 'media', $dependencies);

        $dependencies = $dependencyResolver->getTableDependencies('users');
        $this->assertCount(2, $dependencies);
        $this->assertHasTableDependency('users', 'media', $dependencies);
        $this->assertHasTableDependency('media', 'users', $dependencies);
    }

    /**
     * Test the "getTablesDependencies" method.
     */
    public function testTablesDependencies(): void
    {
        $dependencyResolver = $this->createTableDependencyResolver();

        $dependencies = $dependencyResolver->getTablesDependencies(['stores', 'customers', 'addresses']);
        $this->assertCount(2, $dependencies);
        $this->assertHasTableDependency('addresses', 'customers', $dependencies);
        $this->assertHasTableDependency('customers', 'stores', $dependencies);
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
     * @return TableDependencyResolver
     */
    private function createTableDependencyResolver(): TableDependencyResolver
    {
        $metadataMock = $this->createMock(MysqlMetadata::class);

        // Mock the "getTableNames" method
        $metadataMock->method('getTableNames')
            ->willReturn(['stores', 'customers', 'addresses', 'media', 'users']);

        // Mock the "getForeignKeys" method
        $valueMap = [
            ['stores', []],
            ['customers', [new ForeignKey('fk_stores', 'customers', ['store_id'], 'stores', ['store_id'])]],
            ['addresses', [new ForeignKey('fk_stores', 'addresses', ['customer_id'], 'customers', ['customer_id'])]],
            ['media', [new ForeignKey('fk_users', 'media', ['user_id'], 'users', ['user_id'])]],
            ['users', [new ForeignKey('fk_media', 'users', ['media_id'], 'media', ['media_id'])]],
        ];

        $metadataMock->method('getForeignKeys')
            ->willReturnMap($valueMap);

        /** @var MysqlMetadata $metadataMock */
        return new TableDependencyResolver($metadataMock);
    }
}
