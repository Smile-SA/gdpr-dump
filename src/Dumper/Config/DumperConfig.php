<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Config;

use Smile\GdprDump\Config\ConfigInterface;
use Smile\GdprDump\Dumper\Config\Definition\FakerSettings;
use Smile\GdprDump\Dumper\Config\Definition\FilterPropagationSettings;
use Smile\GdprDump\Dumper\Config\Definition\TableConfig;
use Smile\GdprDump\Dumper\Config\Definition\TableConfigCollection;
use Smile\GdprDump\Dumper\Config\Validation\QueryValidator;

final class DumperConfig implements DumperConfigInterface
{
    private FakerSettings $fakerSettings;
    private FilterPropagationSettings $filterPropagationSettings;
    private TableConfigCollection $tablesConfig;
    private array $dumpSettings;

    /**
     * @var string[]
     */
    private array $varQueries;

    /**
     * @var string[]
     */
    private array $includedTables = [];

    /**
     * @var string[]
     */
    private array $excludedTables = [];

    /**
     * @var string[]
     */
    private array $tablesToTruncate = [];

    /**
     * @var string[]
     */
    private array $tablesToFilter = [];

    /**
     * @var string[]
     */
    private array $tablesToSort = [];

    public function __construct(ConfigInterface $config)
    {
        $this->prepareDumpSettings($config);
        $this->prepareVarQueries($config);
        $this->prepareFakerSettings($config);
        $this->prepareFilterPropagationSettings($config);
        $this->prepareTableSettings($config);
    }

    public function getDumpOutput(): string
    {
        return $this->getDumpSettings()['output'];
    }

    public function getDumpSettings(): array
    {
        return $this->dumpSettings;
    }

    public function getFakerSettings(): FakerSettings
    {
        return $this->fakerSettings;
    }

    public function getFilterPropagationSettings(): FilterPropagationSettings
    {
        return $this->filterPropagationSettings;
    }

    public function getTablesConfig(): TableConfigCollection
    {
        return $this->tablesConfig;
    }

    public function getVarQueries(): array
    {
        return $this->varQueries;
    }

    public function getIncludedTables(): array
    {
        return $this->includedTables;
    }

    public function getExcludedTables(): array
    {
        return $this->excludedTables;
    }

    public function getTablesToTruncate(): array
    {
        return $this->tablesToTruncate;
    }

    public function getTablesToFilter(): array
    {
        return $this->tablesToFilter;
    }

    public function getTablesToSort(): array
    {
        return $this->tablesToSort;
    }

    /**
     * Prepare dump settings.
     */
    private function prepareDumpSettings(ConfigInterface $config): void
    {
        $this->dumpSettings = (array) $config->get('dump', []);

        // Validate init commands
        $queryValidator = new QueryValidator(['set']);
        $initCommands = (array) ($this->dumpSettings['init_commands'] ?? []);

        foreach ($initCommands as $query) {
            $queryValidator->validate($query);
        }
    }

    /**
     * Prepare SQL variables.
     */
    private function prepareVarQueries(ConfigInterface $config): void
    {
        $this->varQueries = (array) $config->get('variables', []);

        // Validate SQL queries
        $queryValidator = new QueryValidator(['select']);
        foreach ($this->varQueries as $query) {
            $queryValidator->validate($query);
        }
    }

    /**
     * Prepare faker settings.
     */
    private function prepareFakerSettings(ConfigInterface $config): void
    {
        $settings = (array) $config->get('faker', []);
        $this->fakerSettings = new FakerSettings((string) ($settings['locale'] ?? ''));
    }

    /**
     * Prepare filter propagation settings.
     */
    private function prepareFilterPropagationSettings(ConfigInterface $config): void
    {
        $settings = (array) $config->get('filter_propagation', []);
        $this->filterPropagationSettings = new FilterPropagationSettings(
            (bool) ($settings['enabled'] ?? true),
            (array) ($settings['ignored_foreign_keys'] ?? [])
        );
    }

    /**
     * Prepare table settings.
     */
    private function prepareTableSettings(ConfigInterface $config): void
    {
        $this->includedTables = (array) $config->get('tables_whitelist', []);
        $this->excludedTables = (array) $config->get('tables_blacklist', []);
        $this->tablesConfig = new TableConfigCollection();
        $tablesData = (array) $config->get('tables', []);

        foreach ($tablesData as $tableName => $tableData) {
            $tableConfig = new TableConfig((string) $tableName, $tableData);
            $this->tablesConfig->add($tableConfig);

            if ($tableConfig->getLimit() === 0) {
                $this->tablesToTruncate[] = $tableConfig->getName();
            }

            if ($tableConfig->hasSortOrder()) {
                $this->tablesToSort[] = $tableConfig->getName();
            }

            if ($tableConfig->hasWhereCondition() || $tableConfig->hasLimit()) {
                $this->tablesToFilter[] = $tableConfig->getName();
            }
        }
    }
}
