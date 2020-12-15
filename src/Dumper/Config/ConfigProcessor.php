<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Config;

use Smile\GdprDump\Config\ConfigInterface;
use Smile\GdprDump\Database\Metadata\MetadataInterface;

class ConfigProcessor
{
    /**
     * @var MetadataInterface
     */
    private $metadata;

    /**
     * @var string[]
     */
    private $tableNames;

    /**
     * @param MetadataInterface $metadata
     */
    public function __construct(MetadataInterface $metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * Process the configuration.
     *
     * @param ConfigInterface $config
     * @return DumperConfig
     */
    public function process(ConfigInterface $config): DumperConfig
    {
        $this->processTableLists($config);
        $this->processTablesData($config);

        return new DumperConfig($config);
    }

    /**
     * Process the tables whitelist and the tables blacklist;
     *
     * @param ConfigInterface $config
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
     * Process the tables data.
     *
     * @param ConfigInterface $config
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
     *
     * @param array $tableNames
     * @return array
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
     *
     * @param array $tablesData
     * @return array
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
     * Get the table names that match a pattern.
     *
     * @param string $pattern
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
