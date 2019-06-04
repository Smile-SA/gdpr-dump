<?php
declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Sql\Schema;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;

class TableDependencyResolver
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var array
     */
    private $foreignKeys;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Get the foreign keys that are related to the specified table.
     *
     * e.g.
     * - with $tableName as "table1"
     * - with foreign keys as: table2 with FK to table 1, table 3 with FK to table 2
     *
     * Result will be:
     * ```
     * [
     *    'table2' => [FK of table 2 to table 1]
     *    'table3' => [FK of table 3 to table 2]
     * ]
     * ```
     *
     * @param string $tableName
     * @return array
     */
    public function getTableDependencies(string $tableName): array
    {
        $this->buildDependencyTree();

        return $this->resolveDependencies($tableName);
    }

    /**
     * Get the foreign keys that are related to the specified tables.
     *
     * @param array $tableNames
     * @return array
     */
    public function getTablesDependencies(array $tableNames): array
    {
        $this->buildDependencyTree();

        $dependencies = [];
        foreach ($tableNames as $tableName) {
            $dependencies = $this->resolveDependencies($tableName, $dependencies);
        }

        return $dependencies;
    }

    /**
     * Recursively fetch all dependencies related to a table.
     *
     * @param string $tableName
     * @param array $resolved
     * @return array
     */
    private function resolveDependencies(string $tableName, array $resolved = []): array
    {
        // No foreign key to this table
        if (!isset($this->foreignKeys[$tableName])) {
            return $resolved;
        }

        $dependencies = $this->foreignKeys[$tableName];

        /** @var ForeignKeyConstraint $dependency */
        foreach ($dependencies as $dependency) {
            $dependencyTable = $dependency->getLocalTableName();

            // Detect cyclic dependencies
            if ($dependencyTable === $tableName) {
                continue;
            }

            $resolved[$dependencyTable][$tableName] = $dependency;
            $resolved = $this->resolveDependencies($dependencyTable, $resolved);
        }

        return $resolved;
    }

    /**
     * Build the tables dependencies (parent -> children).
     */
    private function buildDependencyTree()
    {
        if ($this->foreignKeys !== null) {
            return;
        }

        $tables = $this->connection->getSchemaManager()->listTables();

        foreach ($tables as $table) {
            foreach ($table->getForeignKeys() as $foreignKey) {
                $foreignTableName = $foreignKey->getForeignTableName();
                $this->foreignKeys[$foreignTableName][] = $foreignKey;
            }
        }

        unset($tables);
    }
}
