<?php

declare(strict_types=1);

namespace Smile\GdprDump\Config;

use Smile\GdprDump\Dumper\Config\Validation\QueryValidator;

final class DumperConfigOld
{
    // TODO output
    // TODO tables to filter etc
    private bool $strictSchema = false;
    private FilterPropagationConfig $filterPropagationConfig;
    private FakerConfig $fakerConfig;

    /**
     * @var array<string, mixed>
     */
    private array $connectionParams = [];

    /**
     * @var array<string, mixed>
     */
    private array $dumpSettings = [];

    /**
     * @var array<string, TableConfig>
     */
    private array $tablesConfig = [];

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
    private array $variables = [];

    public function isStrictSchema(): bool
    {
        return $this->strictSchema;
    }

    public function setStrictSchema(bool $strictSchema): self
    {
        $this->strictSchema = $strictSchema;

        return $this;
    }

    /**
     * @return array<string, array|scalar>
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
     * @return array<string, mixed>
     */
    public function getDumpSettings(): array
    {
        return $this->dumpSettings;
    }

    /**
     * @param array<string, array|scalar> $settings
     */
    public function setDumpSettings(array $settings): self
    {
        // Validate init commands
        $queryValidator = new QueryValidator(['set']);
        $initCommands = (array) ($settings['init_commands'] ?? []);

        foreach ($initCommands as $query) {
            $queryValidator->validate($query);
        }

        $this->dumpSettings = $settings;

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
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * @param array<string> $variables
     */
    public function setVariables(array $variables): self
    {
        // Validate SQL queries
        $queryValidator = new QueryValidator(['select']);
        foreach ($variables as $query) {
            $queryValidator->validate($query);
        }

        $this->variables = $variables;

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
}
