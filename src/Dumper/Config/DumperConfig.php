<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Config;

use Druidfi\Mysqldump\Compress\CompressManagerFactory;
use Druidfi\Mysqldump\DumpSettings;
use Smile\GdprDump\Config\ConfigInterface;
use Smile\GdprDump\Dumper\Config\Table\TableConfig;
use Smile\GdprDump\Dumper\Config\Validation\QueryValidator;
use UnexpectedValueException;

class DumperConfig
{
    /**
     * @var TableConfig[]
     */
    private array $tablesConfig = [];

    /**
     * @var string[]
     */
    private array $varQueries = [];

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
    private array $tablesToTruncate = [];

    /**
     * @var string[]
     */
    private array $tablesToFilter = [];

    /**
     * @var string[]
     */
    private array $tablesToSort = [];

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

    private array $filterPropagationSettings = [
        'enabled' => true,
        'ignored_foreign_keys' => [],
    ];

    private array $fakerSettings = [
        'locale' => null,
    ];

    /**
     * @throws UnexpectedValueException
     */
    public function __construct(ConfigInterface $config)
    {
        $this->prepareConfig($config);
    }

    /**
     * Get the dump output.
     */
    public function getDumpOutput(): string
    {
        return $this->dumpSettings['output'];
    }

    /**
     * Get dump settings.
     */
    public function getDumpSettings(): array
    {
        return $this->dumpSettings;
    }

    /**
     * Check whether to apply table filters recursively to table dependencies (by following foreign keys).
     */
    public function isFilterPropagationEnabled(): bool
    {
        return $this->filterPropagationSettings['enabled'];
    }

    /**
     * Get the foreign keys to exclude from the table filter propagation.
     */
    public function getIgnoredForeignKeys(): array
    {
        return $this->filterPropagationSettings['ignored_foreign_keys'];
    }

    /**
     * Get faker settings.
     */
    public function getFakerSettings(): array
    {
        return $this->fakerSettings;
    }

    /**
     * Get the tables configuration (filters, orders, limits).
     *
     * @return TableConfig[]
     */
    public function getTablesConfig(): array
    {
        return $this->tablesConfig;
    }

    /**
     * Get the configuration of a table.
     */
    public function getTableConfig(string $tableName): ?TableConfig
    {
        return $this->tablesConfig[$tableName] ?? null;
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
     * Prepare the config.
     *
     * @throws UnexpectedValueException
     */
    private function prepareConfig(ConfigInterface $config): void
    {
        // Dump settings
        $this->prepareDumpSettings($config);

        // Filter propagation settings
        $this->prepareFilterPropagationSettings($config);

        // Faker settings
        $this->prepareFakerSettings($config);

        // Tables config
        $this->prepareTablesConfig($config);

        // Queries to run
        $this->prepareVarQueries($config);

        // Tables whitelist
        $this->prepareTablesWhitelist($config);

        // Tables blacklist
        $this->prepareTablesBlacklist($config);
    }

    /**
     * Prepare dump settings.
     *
     * @throws UnexpectedValueException
     */
    private function prepareDumpSettings(ConfigInterface $config): void
    {
        $settings = $config->get('dump', []);

        foreach ($settings as $param => $value) {
            if (!array_key_exists($param, $this->dumpSettings)) {
                throw new UnexpectedValueException(sprintf('Invalid dump setting "%s".', $param));
            }

            $this->dumpSettings[$param] = $value;
        }

        // Replace {...} by the current date in dump output
        $this->dumpSettings['output'] = preg_replace_callback(
            '/{([^}]+)}/',
            fn (array $matches) => date($matches[1]),
            $this->dumpSettings['output']
        );
    }

    /**
     * Prepare table filter propagation settings.
     *
     * @throws UnexpectedValueException
     */
    private function prepareFilterPropagationSettings(ConfigInterface $config): void
    {
        $settings = $config->get('filter_propagation', []);

        foreach ($settings as $param => $value) {
            if (!array_key_exists($param, $this->filterPropagationSettings)) {
                throw new UnexpectedValueException(sprintf('Invalid filter propagation setting "%s".', $param));
            }

            $this->filterPropagationSettings[$param] = $value;
        }
    }

    /**
     * Prepare faker settings.
     *
     * @throws UnexpectedValueException
     */
    private function prepareFakerSettings(ConfigInterface $config): void
    {
        $settings = $config->get('faker', []);

        foreach ($settings as $param => $value) {
            if (!array_key_exists($param, $this->fakerSettings)) {
                throw new UnexpectedValueException(sprintf('Invalid faker setting "%s".', $param));
            }

            $this->fakerSettings[$param] = $value;
        }
    }

    /**
     * Prepare the tables configuration.
     *
     * @throws UnexpectedValueException
     */
    private function prepareTablesConfig(ConfigInterface $config): void
    {
        $tablesData = $config->get('tables', []);

        foreach ($tablesData as $tableName => $tableData) {
            $tableName = (string) $tableName;

            $tableConfig = new TableConfig($tableName, $tableData);
            $this->tablesConfig[$tableName] = $tableConfig;

            if ($tableConfig->getLimit() === 0) {
                $this->tablesToTruncate[] = $tableConfig->getName();
            }

            if ($tableConfig->hasSortOrder()) {
                $this->tablesToSort[] = $tableConfig->getName();
            }

            if ($tableConfig->hasFilter() || $tableConfig->hasLimit()) {
                $this->tablesToFilter[] = $tableConfig->getName();
            }
        }
    }

    /**
     * Prepare the SQL queries to run.
     */
    private function prepareVarQueries(ConfigInterface $config): void
    {
        $queryValidator = new QueryValidator();
        $this->varQueries = $config->get('variables', []);

        foreach ($this->varQueries as $index => $query) {
            $queryValidator->validate($query);
            $this->varQueries[$index] = (string) $query;
        }
    }

    /**
     * Prepare the tables whitelist.
     */
    private function prepareTablesWhitelist(ConfigInterface $config): void
    {
        $this->tablesWhitelist = $config->get('tables_whitelist', []);

        foreach ($this->tablesWhitelist as $index => $tableName) {
            $this->tablesWhitelist[$index] = (string) $tableName;
        }
    }

    /**
     * Prepare the tables blacklist.
     */
    private function prepareTablesBlacklist(ConfigInterface $config): void
    {
        $this->tablesBlacklist = $config->get('tables_blacklist', []);

        foreach ($this->tablesBlacklist as $index => $tableName) {
            $this->tablesBlacklist[$index] = (string) $tableName;
        }
    }
}
