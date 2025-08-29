<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration;

use Smile\GdprDump\Configuration\Definition\DumpConfig;
use Smile\GdprDump\Configuration\Definition\FakerConfig;
use Smile\GdprDump\Configuration\Definition\FilterPropagationConfig;
use Smile\GdprDump\Configuration\Definition\TableConfigMap;
use Smile\GdprDump\Configuration\Validator\QueryValidator;

/**
 * Object representation of the dump configuration.
 */
final class Configuration
{
    private bool $strictSchema = false;
    private DumpConfig $dumpConfig;
    private FilterPropagationConfig $filterPropagationConfig;
    private FakerConfig $fakerConfig;
    private TableConfigMap $tableConfigs;

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
    private array $sqlVariables = [];

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

    public function getTableConfigs(): TableConfigMap
    {
        if (!isset($this->tableConfigs)) {
            $this->tableConfigs = new TableConfigMap();
        }

        return $this->tableConfigs;
    }

    public function setTableConfigs(TableConfigMap $tableConfigs): self
    {
        $this->tableConfigs = $tableConfigs;

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
     * @param string[] $tableNames
     */
    public function setExcludedTables(array $tableNames): self
    {
        $this->excludedTables = $tableNames;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getSqlVariables(): array
    {
        return $this->sqlVariables;
    }

    /**
     * @param array<string, string> $sqlVariables
     */
    public function setSqlVariables(array $sqlVariables): self
    {
        // Validate SQL queries
        $queryValidator = new QueryValidator(['select']);
        array_walk($sqlVariables, fn (string $query) => $queryValidator->validate($query));
        $this->sqlVariables = $sqlVariables;

        return $this;
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

        if (isset($this->tableConfigs)) {
            $this->setTableConfigs(clone $this->getTableConfigs());
        }
    }
}
