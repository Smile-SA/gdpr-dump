<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Config;

use RuntimeException;
use Smile\GdprDump\Config\ConfigInterface;
use Smile\GdprDump\Database\Metadata\MetadataInterface;

final class ConfigProcessor
{
    /**
     * @var string[]|null
     */
    private ?array $tableNames = null;

    public function __construct(private MetadataInterface $metadata)
    {
    }

    /**
     * Process the configuration.
     */
    public function process(ConfigInterface $config): DumperConfig
    {
        $this->processTableLists($config);
        $this->processTablesData($config);

        return new DumperConfig($config);
    }

    /**
     * Remove tables that don't exist and resolve patterns (e.g. "log_*") for included/excluded tables.
     */
    private function processTableLists(ConfigInterface $config): void
    {
        $configKeys = ['tables_whitelist', 'tables_blacklist'];

        foreach ($configKeys as $configKey) {
            $tableNames = (array) $config->get($configKey, []);

            if (!empty($tableNames)) {
                $resolved = $this->resolveTableNames($tableNames);
                $config->set($configKey, $resolved);
            }
        }
    }

    /**
     * Remove tables that don't exist from the "tables" parameter,
     * and raise an exception if the remaining tables have converters that reference invalid columns.
     */
    private function processTablesData(ConfigInterface $config): void
    {
        $tablesData = (array) $config->get('tables', []);
        if (!empty($tablesData)) {
            $resolved = $this->resolveTablesData($tablesData);
            $config->set('tables', $resolved);
        }
    }

    /**
     * Resolve a list of table name patterns.
     */
    private function resolveTableNames(array $tableNames): array
    {
        $resolved = [];

        foreach ($tableNames as $tableName) {
            $matches = $this->findTablesByName((string) $tableName);
            if (!empty($matches)) {
                $resolved = array_merge($resolved, $matches);
            }
        }

        return array_unique($resolved);
    }

    /**
     * Resolve table name patterns stored as array keys.
     */
    private function resolveTablesData(array $tablesData): array
    {
        $resolved = [];

        foreach ($tablesData as $tableName => $tableData) {
            $matches = $this->findTablesByName((string) $tableName);

            foreach ($matches as $match) {
                // Throw an exception if a converter refers to a column that does not exist
                $this->validateTableColumns($tableName, $tableData);

                // Merge table configuration
                if (!array_key_exists($match, $resolved)) {
                    $resolved[$match] = [];
                }

                $resolved[$match] += $tableData;
            }
        }

        return $resolved;
    }

    /**
     * Get the table names that match a pattern (e.g. "log_*").
     *
     * @return string[]
     */
    private function findTablesByName(string $pattern): array
    {
        if ($this->tableNames === null) {
            $this->tableNames = $this->metadata->getTableNames();
        }

        $matches = [];

        foreach ($this->tableNames as $tableName) {
            if (fnmatch($pattern, $tableName)) {
                $matches[] = $tableName;
            }
        }

        return $matches;
    }

    /**
     * Raise an exception if the table data contains a converter that references an undefined column.
     *
     * @throws RuntimeException
     */
    private function validateTableColumns(string $tableName, array $tableData): void
    {
        if (!array_key_exists('converters', $tableData) || empty($tableData['converters'])) {
            return;
        }

        $columns = $this->metadata->getColumnNames($tableName);

        foreach ($tableData['converters'] as $columnName => $converterData) {
            $disabled = $converterData['disabled'] ?? false;

            if (!$disabled && !in_array($columnName, $columns)) {
                $message = 'The table "%s" uses a converter on an undefined column "%s".';
                throw new RuntimeException(sprintf($message, $tableName, $columnName));
            }
        }
    }
}
