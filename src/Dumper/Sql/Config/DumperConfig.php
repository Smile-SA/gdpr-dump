<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Dumper\Sql\Config;

use Ifsnop\Mysqldump\Mysqldump;
use Smile\Anonymizer\Config\ConfigInterface;
use Smile\Anonymizer\Dumper\Sql\Config\Table\TableConfig;

class DumperConfig
{
    /**
     * @var TableConfig[]
     */
    private $tablesConfig = [];

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
     * @param ConfigInterface $config
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
     * Get the dump settings.
     *
     * @return array
     */
    public function getDumpSettings(): array
    {
        return $this->dumpSettings;
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
     * @return TableConfig
     */
    public function getTableConfig(string $tableName)
    {
        return $this->tablesConfig[$tableName] ?? null;
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
     */
    private function prepareConfig(ConfigInterface $config)
    {
        // Dump settings
        $this->prepareDumpSettings($config);

        // Tables config
        $this->prepareTablesConfig($config);

        // Tables whitelist
        $this->tablesWhitelist = $config->get('tables_whitelist', []);

        // Tables blacklist
        $this->tablesBlacklist = $config->get('tables_blacklist', []);
    }

    /**
     * Prepare the dump settings.
     *
     * @param ConfigInterface $config
     */
    private function prepareDumpSettings(ConfigInterface $config)
    {
        $settings = $config->get('dump', []);

        foreach ($settings as $param => $value) {
            if (!array_key_exists($param, $this->dumpSettings)) {
                throw new \UnexpectedValueException(sprintf('Invalid dump setting "%s".', $param));
            }

            $this->dumpSettings[$param] = $value;
        }
    }

    /**
     * Prepare the tables configuration.
     *
     * @param ConfigInterface $config
     */
    private function prepareTablesConfig(ConfigInterface $config)
    {
        $tablesData = $config->get('tables', []);

        foreach ($tablesData as $tableName => $tableData) {
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
}
