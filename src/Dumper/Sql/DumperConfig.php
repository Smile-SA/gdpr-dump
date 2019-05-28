<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Dumper\Sql;

use Smile\Anonymizer\Config\ConfigInterface;
use Smile\Anonymizer\Dumper\Sql\Config\DatabaseConfig;
use Smile\Anonymizer\Dumper\Sql\Config\Table\TableConfig;

class DumperConfig
{
    /**
     * @var DatabaseConfig
     */
    private $databaseConfig;

    /**
     * @var string
     */
    private $dumpOutput;

    /**
     * @var array
     */
    private $dumpSettings = [];

    /**
     * @var TableConfig[]
     */
    private $tablesConfig = [];

    /**
     * @var TableFinder
     */
    private $tableFinder;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config, TableFinder $tableFinder)
    {
        $this->tableFinder = $tableFinder;
        $this->prepareConfig($config);
    }

    /**
     * Get the database configuration.
     *
     * @return DatabaseConfig
     */
    public function getDatabase()
    {
        return $this->databaseConfig;
    }

    /**
     * Get the dump output.
     *
     * @return string
     */
    public function getDumpOutput(): string
    {
        return $this->dumpOutput;
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
     * @return TableConfig
     */
    public function getTableConfig(string $tableName)
    {
        return $this->tablesConfig[$tableName] ?? null;
    }

    /**
     * Get the names of the tables to filter.
     *
     * @return string[]
     */
    public function getTablesToFilter(): array
    {
        $tables = [];

        foreach ($this->tablesConfig as $tableConfig) {
            if ($tableConfig->hasFilter() || $tableConfig->hasLimit() || !$tableConfig->isDataDumped()) {
                $tables[] = $tableConfig->getName();
            }
        }

        return $tables;
    }

    /**
     * Get the names of the tables to sort.
     *
     * @return string[]
     */
    public function getTablesToSort(): array
    {
        $tables = [];

        foreach ($this->tablesConfig as $tableConfig) {
            if ($tableConfig->hasSortOrder()) {
                $tables[] = $tableConfig->getName();
            }
        }

        return $tables;
    }

    /**
     * Set the table finder.
     *
     * @param TableFinder $tableFinder
     * @return $this
     */
    public function setTableFinder(TableFinder $tableFinder): DumperConfig
    {
        $this->tableFinder = $tableFinder;

        return $this;
    }

    /**
     * Prepare the config.
     *
     * @param ConfigInterface $config
     */
    private function prepareConfig(ConfigInterface $config)
    {
        // Database config
        $this->databaseConfig = new DatabaseConfig($config->get('database', []));

        // Tables config
        $tablesData = $config->get('tables', []);
        $this->prepareTablesConfig($tablesData);

        // Dump config
        $this->dumpOutput = $config->get('dump.output', 'php://stdout');
        $this->dumpSettings = $config->get('dump.settings', []);
        $this->dumpSettings['exclude-tables'] = $this->getTablesToIgnore();
        $this->dumpSettings['no-data'] = $this->getTablesToTruncate();

        // Change the defaults for some options (to be closed to mysqldump logic)
        $this->dumpSettings += [
            'add-drop-table' => true,
            'lock-tables' => false,
            'hex-blob' => false,
        ];
    }

    /**
     * Prepare the tables config.
     *
     * @param array $tablesData
     */
    private function prepareTablesConfig(array $tablesData)
    {
        foreach ($tablesData as $tableName => $tableData) {
            // Find all tables matching the pattern
            $matches = $this->tableFinder->findByName($tableName);

            // Table found is the same as the table name -> nothing to do
            if (count($matches) === 1 && $matches[0] === $tableName) {
                continue;
            }

            // If tables were found -> update the tables data
            foreach ($matches as $match) {
                if (!array_key_exists($match, $tablesData)) {
                    $tablesData[$match] = [];
                }

                $tablesData[$match] += $tableData;
            }

            // Remove the entry from the tables data
            unset($tablesData[$tableName]);
        }

        foreach ($tablesData as $tableName => $tableData) {
            $this->tablesConfig[$tableName] = new TableConfig($tableName, $tableData);
        }
    }

    /**
     * Get the tables to ignore (not included in the dump file).
     *
     * @return string[]
     */
    private function getTablesToIgnore(): array
    {
        $tables = [];

        foreach ($this->tablesConfig as $tableConfig) {
            if (!$tableConfig->isSchemaDumped()) {
                $tables[] = $tableConfig->getName();
            }
        }

        return $tables;
    }

    /**
     * Get the tables to truncate (only the structure is included in the dump file, not the data).
     *
     * @return string[]
     */
    private function getTablesToTruncate(): array
    {
        $tables = [];

        foreach ($this->tablesConfig as $tableConfig) {
            if (!$tableConfig->isDataDumped()) {
                $tables[] = $tableConfig->getName();
            }
        }

        return $tables;
    }
}
