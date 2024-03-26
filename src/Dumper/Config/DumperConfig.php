<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Config;

use Druidfi\Mysqldump\Compress\CompressManagerFactory;
use Druidfi\Mysqldump\DumpSettings;
use Smile\GdprDump\Config\ConfigInterface;
use Smile\GdprDump\Dumper\Config\Definition\FakerSettings;
use Smile\GdprDump\Dumper\Config\Definition\FilterPropagationSettings;
use Smile\GdprDump\Dumper\Config\Definition\TableConfig;
use Smile\GdprDump\Dumper\Config\Definition\TableConfigCollection;
use Smile\GdprDump\Dumper\Config\Validation\QueryValidator;
use Smile\GdprDump\Dumper\Config\Validation\ValidationException;

class DumperConfig
{
    private FakerSettings $fakerSettings;
    private FilterPropagationSettings $filterPropagationSettings;
    private TableConfigCollection $tablesConfig;
    private array $dumpSettings = [
        'output' => 'php://stdout',
        'add_drop_database' => false,
        'add_drop_table' => true, // false in MySQLDump-PHP
        'add_drop_trigger' => true,
        'add_locks' => true,
        'complete_insert' => false,
        'compress' => CompressManagerFactory::NONE,
        'default_character_set' => DumpSettings::UTF8,
        'disable_keys' => true,
        'events' => false,
        'extended_insert' => true,
        'hex_blob' => false, // true in MySQLDump-PHP
        'init_commands' => [],
        'insert_ignore' => false,
        'lock_tables' => false, // true in MySQLDump-PHP
        'net_buffer_length' => 1000000,
        'no_autocommit' => true,
        'no_create_info' => false,
        'routines' => false,
        'single_transaction' => true,
        'skip_comments' => false,
        'skip_definer' => false,
        'skip_dump_date' => false,
        'skip_triggers' => false,
        'skip_tz_utc' => false,
    ];

    /**
     * @var string[]
     */
    private array $tablesWhitelist = [];

    /**
     * @var string[]
     */
    private array $tablesBlacklist = [];

    /**
     * @var string[]
     */
    private array $varQueries = [];

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

    /**
     * @throws ValidationException
     */
    public function __construct(ConfigInterface $config)
    {
        $this->prepareVarQueries($config);
        $this->prepareDumpSettings($config);
        $this->prepareFakerSettings($config);
        $this->prepareFilterPropagationSettings($config);
        $this->prepareTableSettings($config);
    }

    /**
     * Get the dump output.
     */
    public function getDumpOutput(): string
    {
        return $this->getDumpSettings()['output'];
    }

    /**
     * Get dump settings.
     */
    public function getDumpSettings(): array
    {
        return $this->dumpSettings;
    }

    /**
     * Get faker settings.
     */
    public function getFakerSettings(): FakerSettings
    {
        return $this->fakerSettings;
    }

    /**
     * Get filter propagation settings.
     */
    public function getFilterPropagationSettings(): FilterPropagationSettings
    {
        return $this->filterPropagationSettings;
    }

    /**
     * Get the tables configuration (filters, orders, limits).
     */
    public function getTablesConfig(): TableConfigCollection
    {
        return $this->tablesConfig;
    }

    /**
     * Get the SQL queries to run.
     *
     * The result of each query will then be injected into user-defined variables.
     * Array keys are the variable names, array values are the database queries.
     *
     * @return string[]
     */
    public function getVarQueries(): array
    {
        return $this->varQueries;
    }

    /**
     * Get the tables to whitelist.
     *
     * @return string[]
     */
    public function getTablesWhitelist(): array
    {
        return $this->tablesWhitelist;
    }

    /**
     * Get the tables to blacklist.
     *
     * @return string[]
     */
    public function getTablesBlacklist(): array
    {
        return $this->tablesBlacklist;
    }

    /**
     * Get the tables to truncate (only the structure is included in the dump file, not the data).
     *
     * @return string[]
     */
    public function getTablesToTruncate(): array
    {
        return $this->tablesToTruncate;
    }

    /**
     * Get the names of the tables to filter.
     *
     * @return string[]
     */
    public function getTablesToFilter(): array
    {
        return $this->tablesToFilter;
    }

    /**
     * Get the names of the tables to sort.
     *
     * @return string[]
     */
    public function getTablesToSort(): array
    {
        return $this->tablesToSort;
    }

    /**
     * Prepare SQL variable queries.
     *
     * @throws ValidationException
     */
    private function prepareVarQueries(ConfigInterface $config): void
    {
        $this->varQueries = $config->get('variables', []);

        // Allow only "select" statements in queries
        $selectQueryValidator = new QueryValidator(['select']);
        foreach ($this->varQueries as $query) {
            $selectQueryValidator->validate($query);
        }
    }

    /**
     * Prepare dump settings.
     *
     * @throws ValidationException
     */
    private function prepareDumpSettings(ConfigInterface $config): void
    {
        $settings = $config->get('dump', []);

        foreach ($settings as $param => $value) {
            if (!array_key_exists($param, $this->dumpSettings)) {
                throw new ValidationException(sprintf('Invalid dump setting "%s".', $param));
            }

            $this->dumpSettings[$param] = $value;
        }

        // Allow only "set" statements in init commands
        $initCommandQueryValidator = new QueryValidator(['set']);
        foreach ($this->dumpSettings['init_commands'] as $query) {
            $initCommandQueryValidator->validate($query);
        }
    }

    /**
     * Prepare faker settings.
     */
    private function prepareFakerSettings(ConfigInterface $config): void
    {
        $settings = $config->get('faker', []);
        $this->fakerSettings = new FakerSettings((string) ($settings['locale'] ?? ''));
    }

    /**
     * Prepare filter propagation settings.
     */
    private function prepareFilterPropagationSettings(ConfigInterface $config): void
    {
        $settings = $config->get('filter_propagation', []);

        $this->filterPropagationSettings = new FilterPropagationSettings(
            $settings['enabled'] ?? true,
            $settings['ignored_foreign_keys'] ?? []
        );
    }

    /**
     * Prepare table settings.
     */
    private function prepareTableSettings(ConfigInterface $config): void
    {
        $this->tablesWhitelist = $config->get('tables_whitelist', []);
        $this->tablesBlacklist = $config->get('tables_blacklist', []);
        $this->tablesConfig = new TableConfigCollection();

        foreach ($config->get('tables', []) as $tableName => $tableData) {
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
