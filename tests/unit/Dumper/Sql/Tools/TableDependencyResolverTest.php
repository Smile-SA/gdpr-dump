<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Dumper\Sql\Tools;

use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Smile\GdprDump\Dumper\Sql\Metadata\Definition\Constraint\ForeignKey;
use Smile\GdprDump\Dumper\Sql\Metadata\MysqlMetadata;
use Smile\GdprDump\Dumper\Sql\Tools\TableDependencyResolver;
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
     * Create a table dependency resolver object.
     *
     * @return TableDependencyResolver
     */
    private function createTableDependencyResolver(): TableDependencyResolver
    {
        $metadataMock = $this->createMock(MysqlMetadata::class);

        // Mock the "getTableNames" method
        $metadataMock->method('getTableNames')
            ->willReturn(['stores', 'customers', 'addresses']);

        // Mock the "getForeignKeys" method
        $valueMap = [
            ['stores', []],
            ['customers', [new ForeignKey('fk_stores', 'customers', ['store_id'], 'stores', ['store_id'])]],
            ['addresses', [new ForeignKey('fk_stores', 'addresses', ['customer_id'], 'customers', ['customer_id'])]],
        ];

        $metadataMock->method('getForeignKeys')
            ->willReturnMap($valueMap);

        return new TableDependencyResolver($metadataMock);
    }
}
