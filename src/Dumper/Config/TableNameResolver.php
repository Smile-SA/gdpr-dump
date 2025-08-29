<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Config;

use Smile\GdprDump\Configuration\Configuration;
use Smile\GdprDump\Configuration\Definition\TableConfig;
use Smile\GdprDump\Configuration\Definition\TableConfigMap;
use Smile\GdprDump\Database\Exception\MetadataException;
use Smile\GdprDump\Database\Metadata\DatabaseMetadata;

final class TableNameResolver
{
    /**
     * @var string[]|null
     */
    private ?array $tableNames = null;

    public function __construct(private DatabaseMetadata $metadata)
    {
    }

    /**
     * Process the configuration by performing the following actions:
     *
     * - Resolving table patterns (e.g. "log_*").
     * - Removing undefined tables if strict_schema option is disabled in the configuration,
         validating table names otherwise.
     * - Validating column names.
     *
     * @throws MetadataException if there are invalid column names (and table names when strict_schema is enabled)
     */
    public function process(Configuration $configuration): void
    {
        $this->processTableLists($configuration);
        $this->processTablesConfig($configuration);
    }

    /**
     * Process the `tables_whitelist` and `tables_blacklist` parameters.
     */
    private function processTableLists(Configuration $configuration): void
    {
        $strict = $configuration->isStrictSchema();

        $includedTables = $configuration->getIncludedTables();
        if ($includedTables) {
            $configuration->setIncludedTables($this->resolveTableNames($includedTables, $strict));
        }

        $excludedTables = $configuration->getExcludedTables();
        if ($excludedTables) {
            $configuration->setExcludedTables($this->resolveTableNames($excludedTables, $strict));
        }
    }

    /**
     * Process the `tables` parameter.
     */
    private function processTablesConfig(Configuration $configuration): void
    {
        $tableConfigs = $configuration->getTableConfigs();

        if (!$tableConfigs->isEmpty()) {
            $strict = $configuration->isStrictSchema();
            $configuration->setTableConfigs($this->resolveTableConfigs($tableConfigs, $strict));
        }
    }

    /**
     * Resolve a list of table name patterns.
     *
     * @param string[] $tableNames
     */
    private function resolveTableNames(array $tableNames, bool $strict): array
    {
        $resolved = [];

        foreach ($tableNames as $tableName) {
            $matches = $this->findTablesByName($tableName, $strict);
            if ($matches) {
                $resolved = array_merge($resolved, $matches);
            }
        }

        return array_unique($resolved);
    }

    /**
     * Resolve table name patterns stored as array keys.
     */
    private function resolveTableConfigs(TableConfigMap $tableConfigs, bool $strict): TableConfigMap
    {
        $resolved = new TableConfigMap();

        foreach ($tableConfigs as $tableName => $tableConfig) {
            $matches = $this->findTablesByName($tableName, $strict);
            $matchCount = count($matches);

            // A pattern was matched, merge the data of each match to existing tables (or create new entries)
            foreach ($matches as $match) {
                $this->validateTableColumns($match, $tableConfig);
                $this->validateSortOrders($match, $tableConfig);

                if ($matchCount > 1) {
                    // Avoid using the same object instance for multiple matches
                    $tableConfig = clone $tableConfig;
                }

                $itemToMerge = $resolved->get($match);
                if (!$itemToMerge) {
                    $resolved->set($match, $tableConfig);
                    continue;
                }

                // Table config of current loop iteration is merged into the existing resolved data
                $resolved->set($match, $itemToMerge->shallowMerge($tableConfig));
            }
        }

        return $resolved;
    }

    /**
     * Get the table names that match a pattern (e.g. "log_*").
     *
     * @return string[]
     */
    private function findTablesByName(string $pattern, bool $strict): array
    {
        $this->tableNames ??= $this->metadata->getTableNames();
        $matches = [];

        foreach ($this->tableNames as $tableName) {
            if (fnmatch($pattern, $tableName)) {
                $matches[] = $tableName;
            }
        }

        if (!$matches && $strict) {
            throw new MetadataException(sprintf('No table found with pattern "%s".', $pattern));
        }

        return $matches;
    }

    /**
     * Raise an exception if the table data contains a converter that references an undefined column.
     */
    private function validateTableColumns(string $tableName, TableConfig $tableConfig): void
    {
        $converterConfigs = $tableConfig->getConverterConfigs();
        if ($converterConfigs->isEmpty()) {
            return;
        }

        $columns = $this->metadata->getColumnNames($tableName);

        foreach ($converterConfigs->getKeys() as $columnName) {
            if (!in_array($columnName, $columns, true)) {
                $message = 'The table "%s" uses a converter on an undefined column "%s".';
                throw new MetadataException(sprintf($message, $tableName, $columnName));
            }
        }
    }

    /**
     * Raise an exception if a sort order references an undefined column.
     */
    private function validateSortOrders(string $tableName, TableConfig $tableConfig): void
    {
        $columns = $this->metadata->getColumnNames($tableName);

        foreach ($tableConfig->getSortOrders() as $sortOrder) {
            if (!in_array($sortOrder->getColumn(), $columns, true)) {
                $message = 'The table "%s" uses a sort order on an undefined column "%s".';
                throw new MetadataException(sprintf($message, $tableName, $sortOrder->getColumn()));
            }
        }
    }
}
