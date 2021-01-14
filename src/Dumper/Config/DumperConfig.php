<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Config;

use Ifsnop\Mysqldump\Mysqldump;
use Smile\GdprDump\Config\ConfigInterface;
use Smile\GdprDump\Dumper\Config\Table\TableConfig;
use Smile\GdprDump\Dumper\Config\Validation\QueryValidator;
use UnexpectedValueException;

class DumperConfig
{
    /**
     * @var TableConfig[]
     */
    private $tablesConfig = [];

    /**
     * @var string[]
     */
    private $varQueries = [];

    /**
     * @var string[]
     */
    private $tablesWhitelist = [];

    /**
     * @var string[]
     */
    private $tablesBlacklist = [];

    /**
     * @var string[]
     */
    private $tablesToTruncate = [];

    /**
     * @var string[]
     */
    private $tablesToFilter = [];

    /**
     * @var string[]
     */
    private $tablesToSort = [];

    /**
     * @var array
     */
    private $dumpSettings = [
        'output' => 'php://stdout',
        'compress' => Mysqldump::NONE,
        'init_commands' => [],
        'reset_auto_increment' => false,
        'add_drop_database' => false,
        'add_drop_table' => true, // false in MySQLDump-PHP
        'add_drop_trigger' => true,
        'add_locks' => true,
        'complete_insert' => false,
        'default_character_set' => Mysqldump::UTF8,
        'disable_keys' => true,
        'extended_insert' => true,
        'events' => false,
        'hex_blob' => false, // true in MySQLDump-PHP
        'insert_ignore' => false,
        'net_buffer_length' => Mysqldump::MAXLINESIZE,
        'no_autocommit' => true,
        'no_create_info' => false,
        'lock_tables' => false, // true in MySQLDump-PHP
        'routines' => false,
        'single_transaction' => true,
        'skip_triggers' => false,
        'skip_tz_utc' => false,
        'skip_comments' => false,
        'skip_dump_date' => false,
        'skip_definer' => false,
    ];

    /**
     * @var array
     */
    private $fakerSettings = [
        'locale' => null,
    ];

    /**
     * @param ConfigInterface $config
     * @throws UnexpectedValueException
     */
    public function __construct(ConfigInterface $config)
    {
        $this->prepareConfig($config);
    }

    /**
     * Get the dump output.
     *
     * @return string
     */
    public function getDumpOutput(): string
    {
        return $this->dumpSettings['output'];
    }

    /**
     * Get dump settings.
     *
     * @return array
     */
    public function getDumpSettings(): array
    {
        return $this->dumpSettings;
    }

    /**
     * Get faker settings.
     *
     * @return array
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
     *
     * @param string $tableName
     * @return TableConfig|null
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
     * @param ConfigInterface $config
     * @throws UnexpectedValueException
     */
    private function prepareConfig(ConfigInterface $config): void
    {
        // Dump settings
        $this->prepareDumpSettings($config);

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
     * @param ConfigInterface $config
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
            function (array $matches): string {
                return date($matches[1]);
            },
            $this->dumpSettings['output']
        );
    }

    /**
     * Prepare faker settings.
     *
     * @param ConfigInterface $config
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
     * @param ConfigInterface $config
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
     *
     * @param ConfigInterface $config
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
     *
     * @param ConfigInterface $config
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
     *
     * @param ConfigInterface $config
     */
    private function prepareTablesBlacklist(ConfigInterface $config): void
    {
        $this->tablesBlacklist = $config->get('tables_blacklist', []);

        foreach ($this->tablesBlacklist as $index => $tableName) {
            $this->tablesBlacklist[$index] = (string) $tableName;
        }
    }
}
