<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Tools;

use Smile\GdprDump\Database\Metadata\Definition\Constraint\ForeignKey;
use Smile\GdprDump\Database\Metadata\MetadataInterface;

class TableDependencyResolver
{
    /**
     * @var MetadataInterface
     */
    private $metadata;

    /**
     * @var ForeignKey[]
     */
    private $foreignKeys;

    /**
     * @param MetadataInterface $metadata
     */
    public function __construct(MetadataInterface $metadata)
    {
        $this->metadata = $metadata;
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

        $foreignKeys = $this->foreignKeys[$tableName];

        /** @var ForeignKey $foreignKey */
        foreach ($foreignKeys as $foreignKey) {
            $dependencyTable = $foreignKey->getLocalTableName();

            // Detect cyclic dependencies
            if ($dependencyTable === $tableName) {
                continue;
            }

            $resolved[$dependencyTable][$tableName] = $foreignKey;
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

        $tableNames = $this->metadata->getTableNames();

        foreach ($tableNames as $tableName) {
            $foreignKeys = $this->metadata->getForeignKeys($tableName);

            foreach ($foreignKeys as $foreignKey) {
                $foreignTableName = $foreignKey->getForeignTableName();
                $this->foreignKeys[$foreignTableName][] = $foreignKey;
            }
        }
    }
}
