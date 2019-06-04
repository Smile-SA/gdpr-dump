<?php
declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Sql\Schema;

use Doctrine\DBAL\Connection;

class TableFinder
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var array
     */
    private $tableNames;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Get the table names that match the pattern.
     *
     * @param string $pattern
     * @return array
     */
    public function findByName(string $pattern): array
    {
        $matches = [];

        foreach ($this->getTableNames() as $tableName) {
            if (fnmatch($pattern, $tableName)) {
                $matches[] = $tableName;
            }
        }

        return $matches;
    }

    /**
     * Get all table names.
     *
     * @return array
     */
    private function getTableNames(): array
    {
        if ($this->tableNames !== null) {
            return $this->tableNames;
        }

        $this->tableNames = $this->connection->getSchemaManager()->listTableNames();

        return $this->tableNames;
    }
}
