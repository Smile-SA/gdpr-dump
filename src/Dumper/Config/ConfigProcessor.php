<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Config;

use Smile\GdprDump\Config\ConfigInterface;
use Smile\GdprDump\Database\Metadata\MetadataInterface;

class ConfigProcessor
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
     * Remote tables that don't exist and resolve patterns (e.g. "log_*") for the table whitelist/blacklist.
     */
    private function processTableLists(ConfigInterface $config): void
    {
        $configKeys = ['tables_whitelist', 'tables_blacklist'];

        foreach ($configKeys as $configKey) {
            $tableNames = $config->get($configKey, []);

            if (!empty($tableNames)) {
                $resolved = $this->resolveTableNames($tableNames);
                $config->set($configKey, $resolved);
            }
        }
    }

    /**
     * Remove tables that don't exist from the "tables" parameter.
     */
    private function processTablesData(ConfigInterface $config): void
    {
        $tablesData = $config->get('tables', []);
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
}
