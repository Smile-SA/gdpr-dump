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
     * @var string[]
     */
    private $tablesToIgnore = [];

    /**
     * @var string[]
     */
    private $tablesToTruncate = [];

    /**
     * @var array
     */
    private $tablesConfig = [];

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
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
     * Get the tables to ignore (not included in the dump file).
     *
     * @return string[]
     */
    public function getTablesToIgnore(): array
    {
        return $this->tablesToIgnore;
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
    public function getTableConfig(string $tableName): TableConfig
    {
        return $this->tablesConfig[$tableName];
    }

    /**
     * Prepare the config.
     *
     * @param ConfigInterface $config
     */
    private function prepareConfig(ConfigInterface $config)
    {
        // Database config
        $this->databaseConfig = new DatabaseConfig($config);

        // Tables config
        $tablesData = $config->get('tables', []);
        foreach ($tablesData as $tableName => $tableData) {
            $this->prepareTableConfig($tableName, $tableData);
        }

        // Dump config
        $this->dumpOutput = $config->get('dump.output', 'php://stdout');
        $this->dumpSettings = $config->get('dump.settings', []);
        $this->dumpSettings['exclude-tables'] = $this->getTablesToIgnore();
        $this->dumpSettings['no-data'] = $this->getTablesToTruncate();

        $this->dumpSettings += [
            'add-drop-table' => true,
        ];
    }

    /**
     * Prepare the table config.
     *
     * @param string $tableName
     * @param array $tableData
     */
    private function prepareTableConfig(string $tableName, array $tableData)
    {
        if (isset($tableData['ignore']) && $tableData['ignore']) {
            $this->tablesToIgnore[] = $tableName;
        }

        if (isset($tableData['truncate']) && $tableData['truncate']) {
            $this->tablesToTruncate[] = $tableName;
        }

        $this->tablesConfig[$tableName] = new TableConfig($tableName, $tableData);
    }
}
