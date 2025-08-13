<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config;

use Smile\GdprDump\Config\Definition\DumpConfig;
use Smile\GdprDump\Config\Definition\FakerConfig;
use Smile\GdprDump\Config\Definition\FilterPropagationConfig;
use Smile\GdprDump\Config\Definition\TableConfig;

interface DumperConfigInterface
{
    public function isStrictSchema(): bool;

    public function setStrictSchema(bool $strictSchema): self;

    public function getDumpSettings(): DumpConfig;

    /**
     * @param array<string, array|scalar> $settings
     */
    public function setDumpSettings(DumpConfig $dumpConfig): self;

    public function getFilterPropagationConfig(): FilterPropagationConfig;

    public function setFilterPropagationConfig(FilterPropagationConfig $filterPropagation): self;

    public function getFakerConfig(): FakerConfig;

    public function setFakerConfig(FakerConfig $fakerConfig): self;

    /**
     * @return array<string, TableConfig>
     */
    public function getTablesConfig(): array;

    /**
     * @param array<string, TableConfig> $tablesConfig
     */
    public function setTablesConfig(array $tablesConfig): self;

    /**
     * @return array<string, mixed>
     */
    public function getConnectionParams(): array;

    /**
     * @param array<string, mixed> $params
     */
    public function setConnectionParams(array $params): self;

    /**
     * @return string[]
     */
    public function getIncludedTables(): array;

    /**
     * @param string[] $tableNames
     */
    public function setIncludedTables(array $tableNames): self;

    /**
     * @return string[]
     */
    public function getExcludedTables(): array;

    /**
     * @param array<string, string> $tableNames
     */
    public function setExcludedTables(array $tableNames): self;

    /**
     * @return array<string, string>
     */
    public function getVarQueries(): array;

    /**
     * @param array<string> $variables
     */
    public function setVarQueries(array $variables): self;

    /**
     * @return string[]
     */
    public function getTablesToFilter(): array;

    /**
     * @return string[]
     */
    public function getTablesToSort(): array;

    /**
     * @return string[]
     */
    public function getTablesToTruncate(): array;
}
