<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Config;

use RuntimeException;
use Smile\GdprDump\Config\Definition\TableConfig;
use Smile\GdprDump\Config\DumperConfig;
use Smile\GdprDump\Database\Metadata\DatabaseMetadata;

final class ConfigProcessor
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
     * - Removing tables that don't exist.
     * - Resolving table patterns (e.g. "log_*").
     * - Validating table names (if strict_schema option is enabled in the configuration).
     * - Validating column names.
     *
     * @throws RuntimeException if there are invalid column names
     */
    public function process(DumperConfig $config): void
    {
        $this->processTableLists($config);
        $this->processTablesConfig($config);
    }

    /**
     * Process the `tables_whitelist` and `tables_blacklist` parameters.
     */
    private function processTableLists(DumperConfig $config): void
    {
        $strict = $config->isStrictSchema();

        $includedTables = $config->getIncludedTables();
        if ($includedTables) {
            $config->setIncludedTables($this->resolveTableNames($includedTables, $strict));
        }

        $excludedTables = $config->getExcludedTables();
        if ($excludedTables) {
            $config->setExcludedTables($this->resolveTableNames($excludedTables, $strict));
        }
    }

    /**
     * Process the `tables` parameter.
     */
    private function processTablesConfig(DumperConfig $config): void
    {
        $tablesConfig = $config->getTablesConfig();

        if ($tablesConfig) {
            $strict = $config->isStrictSchema();
            $config->setTablesConfig($this->resolveTablesConfig($tablesConfig, $strict));
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
            $matches = $this->findTablesByName((string) $tableName, $strict);
            if ($matches) {
                $resolved = array_merge($resolved, $matches);
            }
        }

        return array_unique($resolved);
    }

    /**
     * Resolve table name patterns stored as array keys.
     *
     * @param array<string, TableConfig> $tablesConfig
     * @throws RuntimeException
     */
    private function resolveTablesConfig(array $tablesConfig, bool $strict): array
    {
        /** @var array<string, TableConfig> $resolved */
        $resolved = [];

        foreach ($tablesConfig as $tableName => $tableConfig) {
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

                if (!array_key_exists($match, $resolved)) {
                    $resolved[$match] = $tableConfig;
                    continue;
                }

                // Table config of current loop iteration is merged into the existing resolved data
                $resolved[$match] = $resolved[$match]->shallowMerge($tableConfig);
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
            throw new RuntimeException(sprintf('No table found with pattern "%s".', $pattern));
        }

        return $matches;
    }

    /**
     * Raise an exception if the table data contains a converter that references an undefined column.
     *
     * @throws RuntimeException
     */
    private function validateTableColumns(string $tableName, TableConfig $tableConfig): void
    {
        $convertersConfig = $tableConfig->getConvertersConfig();
        if (!$convertersConfig) {
            return;
        }

        $columns = $this->metadata->getColumnNames($tableName);

        foreach (array_keys($convertersConfig) as $columnName) {
            if (!in_array($columnName, $columns, true)) {
                $message = 'The table "%s" uses a converter on an undefined column "%s".';
                throw new RuntimeException(sprintf($message, $tableName, $columnName));
            }
        }
    }

    /**
     * Raise an exception if a sort order references an undefined column.
     *
     * @throws RuntimeException
     */
    private function validateSortOrders(string $tableName, TableConfig $tableConfig): void
    {
        $columns = $this->metadata->getColumnNames($tableName);

        foreach ($tableConfig->getSortOrders() as $sortOrder) {
            if (!in_array($sortOrder->getColumn(), $columns, true)) {
                $message = 'The table "%s" uses a sort order on an undefined column "%s".';
                throw new RuntimeException(sprintf($message, $tableName, $sortOrder->getColumn()));
            }
        }
    }
}
