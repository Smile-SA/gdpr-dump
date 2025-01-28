<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Config;

use Smile\GdprDump\Config\ConfigInterface;
use Smile\GdprDump\Dumper\Config\Definition\FakerSettings;
use Smile\GdprDump\Dumper\Config\Definition\FilterPropagationSettings;
use Smile\GdprDump\Dumper\Config\Definition\TableConfig;
use Smile\GdprDump\Dumper\Config\Definition\TableConfigCollection;

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
        $this->varQueries = (array) $config->get('variables', []);
        $this->dumpSettings = (array) $config->get('dump', []);
        $this->prepareFakerSettings($config);
        $this->prepareFilterPropagationSettings($config);
        $this->prepareTableSettings($config);
    }

    /**
     * @inheritdoc
     */
    public function getDumpOutput(): string
    {
        return $this->getDumpSettings()['output'];
    }

    /**
     * @inheritdoc
     */
    public function getDumpSettings(): array
    {
        return $this->dumpSettings;
    }

    /**
     * @inheritdoc
     */
    public function getFakerSettings(): FakerSettings
    {
        return $this->fakerSettings;
    }

    /**
     * @inheritdoc
     */
    public function getFilterPropagationSettings(): FilterPropagationSettings
    {
        return $this->filterPropagationSettings;
    }

    /**
     * @inheritdoc
     */
    public function getTablesConfig(): TableConfigCollection
    {
        return $this->tablesConfig;
    }

    /**
     * @inheritdoc
     */
    public function getVarQueries(): array
    {
        return $this->varQueries;
    }

    /**
     * @inheritdoc
     */
    public function getIncludedTables(): array
    {
        return $this->includedTables;
    }

    /**
     * @inheritdoc
     */
    public function getExcludedTables(): array
    {
        return $this->excludedTables;
    }

    /**
     * @inheritdoc
     */
    public function getTablesToTruncate(): array
    {
        return $this->tablesToTruncate;
    }

    /**
     * @inheritdoc
     */
    public function getTablesToFilter(): array
    {
        return $this->tablesToFilter;
    }

    /**
     * @inheritdoc
     */
    public function getTablesToSort(): array
    {
        return $this->tablesToSort;
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
