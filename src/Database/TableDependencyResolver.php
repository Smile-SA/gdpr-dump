<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database;

use Smile\GdprDump\Database\Metadata\Definition\Constraint\ForeignKey;
use Smile\GdprDump\Database\Metadata\MetadataInterface;
use Smile\GdprDump\Dumper\Config\DumperConfig;

class TableDependencyResolver
{
    private bool $resolved = false;

    /**
     * Foreign keys by referenced table name.
     * E.g. key "table1" will contain an array with all foreign key that reference that table.
     *
     * @var ForeignKey[][]
     */
    private array $foreignKeys = [];

    public function __construct(private MetadataInterface $metadata, private DumperConfig $config)
    {
    }

    /**
     * Get all dependencies of the specified tables:
     * - Tables with foreign key(s) that reference one of the specified tables.
     * - Tables with foreign key(s) that reference a table which depends on the specified tables (dependency chain).
     *
     * Example with the following foreign keys:
     * - table1: no foreign key
     * - table2: foreign key "fk_t2" to table 1
     * - table3: foreign key "fk_t3" to table 2
     *
     * If `$tableNames` is `['table1', 'table2', 'table3']`, the returned array is:
     * ```
     * [
     *    'table2' => [
     *        'fk_t2' => FK object,
     *    ],
     *    'table3' => [
     *        'fk_t3' => FK object,
     *    ],
     * ]
     * ```
     *
     * Same result if `$tableNames` is `['table1']`.
     * If `$tableNames` is `['table2']`, the result array has a single key `'table3'`.
     * If `$tableNames` is `['table3']`, the result array is empty.
     */
    public function getDependencies(array $tableNames): array
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
     */
    private function resolveDependencies(string $tableName, array $resolved = []): array
    {
        // No foreign key to this table
        if (!isset($this->foreignKeys[$tableName])) {
            return $resolved;
        }

        $foreignKeys = $this->foreignKeys[$tableName];

        foreach ($foreignKeys as $foreignKey) {
            $dependencyTable = $foreignKey->getLocalTableName();
            $fkName = $foreignKey->getConstraintName();

            // Stop recursion when a cyclic dependency is detected
            if (isset($resolved[$dependencyTable][$fkName])) {
                continue;
            }

            $resolved[$dependencyTable][$fkName] = $foreignKey;
            $resolved = $this->resolveDependencies($dependencyTable, $resolved);
        }

        return $resolved;
    }

    /**
     * Build the tables dependencies (parent -> children).
     */
    private function buildDependencyTree(): void
    {
        if ($this->resolved) {
            return;
        }

        $tableNames = $this->metadata->getTableNames();

        foreach ($tableNames as $tableName) {
            $foreignKeys = $this->metadata->getTableForeignKeys($tableName);

            foreach ($foreignKeys as $foreignKey) {
                if (!$this->isForeignKeyIgnored($foreignKey)) {
                    $foreignTableName = $foreignKey->getForeignTableName();
                    $this->foreignKeys[$foreignTableName][] = $foreignKey;
                }
            }
        }

        $this->resolved = true;
    }

    /**
     * Check whether the foreign key must be skipped.
     */
    private function isForeignKeyIgnored(ForeignKey $foreignKey): bool
    {
        return in_array(
            $foreignKey->getConstraintName(),
            $this->config->getFilterPropagationSettings()->getIgnoredForeignKeys(),
            true
        );
    }
}
