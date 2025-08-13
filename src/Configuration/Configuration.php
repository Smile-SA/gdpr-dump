<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration;

use Smile\GdprDump\Configuration\Definition\DumpConfig;
use Smile\GdprDump\Configuration\Definition\FakerConfig;
use Smile\GdprDump\Configuration\Definition\FilterPropagationConfig;
use Smile\GdprDump\Configuration\Definition\TableConfig;
use Smile\GdprDump\Configuration\Validator\QueryValidator;

final class Configuration
{
    private bool $strictSchema = false;
    private DumpConfig $dumpConfig;
    private FilterPropagationConfig $filterPropagationConfig;
    private FakerConfig $fakerConfig;

    /**
     * @var array<string, TableConfig>
     */
    private array $tablesConfig = [];

    /**
     * @var array<string, mixed>
     */
    private array $connectionParams = [];

    /**
     * @var string[]
     */
    private array $includedTables = [];

    /**
     * @var string[]
     */
    private array $excludedTables = [];

    /**
     * @var array<string, string>
     */
    private array $varQueries = [];

    public function isStrictSchema(): bool
    {
        return $this->strictSchema;
    }

    public function setStrictSchema(bool $strictSchema): self
    {
        $this->strictSchema = $strictSchema;

        return $this;
    }

    public function getDumpSettings(): DumpConfig
    {
        if (!isset($this->dumpConfig)) {
            $this->dumpConfig = new DumpConfig();
        }

        return $this->dumpConfig;
    }

    /**
     * @param array<string, array|scalar> $settings
     */
    public function setDumpSettings(DumpConfig $dumpConfig): self
    {
        $this->dumpConfig = $dumpConfig;

        return $this;
    }

    public function getFilterPropagationConfig(): FilterPropagationConfig
    {
        if (!isset($this->filterPropagationConfig)) {
            $this->filterPropagationConfig = new FilterPropagationConfig();
        }

        return $this->filterPropagationConfig;
    }

    public function setFilterPropagationConfig(FilterPropagationConfig $filterPropagation): self
    {
        $this->filterPropagationConfig = $filterPropagation;

        return $this;
    }

    public function getFakerConfig(): FakerConfig
    {
        if (!isset($this->fakerConfig)) {
            $this->fakerConfig = new FakerConfig();
        }

        return $this->fakerConfig;
    }

    public function setFakerConfig(FakerConfig $fakerConfig): self
    {
        $this->fakerConfig = $fakerConfig;

        return $this;
    }

    /**
     * @return array<string, TableConfig>
     */
    public function getTablesConfig(): array
    {
        return $this->tablesConfig;
    }

    /**
     * @param array<string, TableConfig> $tablesConfig
     */
    public function setTablesConfig(array $tablesConfig): self
    {
        $this->tablesConfig = $tablesConfig;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getConnectionParams(): array
    {
        return $this->connectionParams;
    }

    /**
     * @param array<string, mixed> $params
     */
    public function setConnectionParams(array $params): self
    {
        $this->connectionParams = $params;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getIncludedTables(): array
    {
        return $this->includedTables;
    }

    /**
     * @param string[] $tableNames
     */
    public function setIncludedTables(array $tableNames): self
    {
        $this->includedTables = $tableNames;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getExcludedTables(): array
    {
        return $this->excludedTables;
    }

    /**
     * @param array<string, string> $tableNames
     */
    public function setExcludedTables(array $tableNames): self
    {
        $this->excludedTables = $tableNames;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getVarQueries(): array
    {
        return $this->varQueries;
    }

    /**
     * @param array<string> $variables
     */
    public function setVarQueries(array $variables): self
    {
        // Validate SQL queries
        $queryValidator = new QueryValidator(['select']);
        array_walk($variables, fn (string $query) => $queryValidator->validate($query));
        $this->varQueries = $variables;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getTablesToFilter(): array
    {
        $filter = fn (TableConfig $tableConfig) => $tableConfig->getWhere() !== '' || $tableConfig->getLimit() > 0;

        return array_keys(array_filter($this->getTablesConfig(), $filter));
    }

    /**
     * @return string[]
     */
    public function getTablesToSort(): array
    {
        $filter = fn (TableConfig $tableConfig) => (bool) $tableConfig->getSortOrders();

        return array_keys(array_filter($this->getTablesConfig(), $filter));
    }

    /**
     * @return string[]
     */
    public function getTablesToTruncate(): array
    {
        $filter = fn (TableConfig $tableConfig) => (bool) $tableConfig->isTruncate();

        return array_keys(array_filter($this->getTablesConfig(), $filter));
    }

    /**
     * Deep clone the object.
     */
    public function __clone(): void
    {
        if (isset($this->dumpConfig)) {
            $this->setDumpSettings(clone $this->getDumpSettings());
        }

        if (isset($this->fakerConfig)) {
            $this->setFakerConfig(clone $this->getFakerConfig());
        }

        if (isset($this->filterPropagationConfig)) {
            $this->setFilterPropagationConfig(clone $this->getFilterPropagationConfig());
        }

        $this->setTablesConfig(array_map(fn (TableConfig $item) => clone $item, $this->getTablesConfig()));
    }
}
