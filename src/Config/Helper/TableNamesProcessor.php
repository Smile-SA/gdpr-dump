<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config\Helper;

use Smile\GdprDump\Config\Exception\TableResolverException;
use Smile\GdprDump\Database\Metadata\DatabaseMetadata;
use Smile\GdprDump\Util\Objects;
use stdClass;

// TODO unit tests? or already implemented (it's just the ConfigProcessor renamed and slightly refactored)
final class TableNamesProcessor
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
     * @throws TableResolverException if there are invalid column names
     */
    public function process(stdClass $config): void
    {
        $this->processTableLists($config);
        $this->processTablesConfig($config);
    }

    /**
     * Process the `tables_whitelist` and `tables_blacklist` parameters.
     */
    private function processTableLists(stdClass $config): void
    {
        $strict = $this->isStrictSchema($config);

        $includedTables = Objects::getProperty($config, 'tables_whitelist', 'array');
        if ($includedTables) {
            $config->tables_whitelist = $this->resolveTableNames($includedTables, $strict);
        }

        $excludedTables = Objects::getProperty($config, 'tables_blacklist', 'array');
        if ($excludedTables) {
            $config->tables_blacklist = $this->resolveTableNames($excludedTables, $strict);
        }
    }

    /**
     * Process the `tables` parameter.
     */
    private function processTablesConfig(stdClass $config): void
    {
        $tablesConfig = (array) Objects::getProperty($config, 'tables', '?object');

        if ($tablesConfig) {
            $strict = $this->isStrictSchema($config);
            $config->tables = $this->resolveTablesConfig($tablesConfig, $strict);
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
     */
    private function resolveTablesConfig(array $tablesConfig, bool $strict): stdClass
    {
        $resolved = new stdClass();

        foreach ($tablesConfig as $tableName => $tableConfig) {
            if ($tableConfig === null) {
                continue; // object properties can be set to null in the config file
            }

            $matches = $this->findTablesByName($tableName, $strict);

            foreach ($matches as $match) {
                // Throw an exception if a converter refers to a column that does not exist
                $this->validateTableColumns($tableName, $tableConfig);

                // Merge table configuration
                if (!property_exists($resolved, $match)) {
                    $resolved->$match = clone $tableConfig;
                } else {
                    Objects::merge($resolved->$match, $tableConfig);
                }
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
            throw new TableResolverException(sprintf('No table found with pattern "%s".', $pattern));
        }

        return $matches;
    }

    /**
     * Raise an exception if the table data contains a converter that references an undefined column.
     */
    private function validateTableColumns(string $tableName, stdClass $tableConfig): void
    {
        $convertersConfig = (array) Objects::getProperty($tableConfig, 'converters', '?object');
        if (!$convertersConfig) {
            return;
        }

        $columns = $this->metadata->getColumnNames($tableName);

        foreach (array_keys($convertersConfig) as $columnName) {
            if (!in_array($columnName, $columns, true)) {
                $message = 'The table "%s" uses a converter on an undefined column "%s".';
                throw new TableResolverException(sprintf($message, $tableName, $columnName));
            }
        }
    }

    /**
     * Check whether strict schema mode is enabled.
     */
    private function isStrictSchema(stdClass $config): bool
    {
        return (bool) Objects::getProperty($config, 'strict_schema', 'bool');
    }
}
