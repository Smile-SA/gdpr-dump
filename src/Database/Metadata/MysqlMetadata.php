<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database\Metadata;

use Doctrine\DBAL\Connection;
use Smile\GdprDump\Database\Metadata\Definition\Constraint\ForeignKey;

class MysqlMetadata implements MetadataInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $schema;

    /**
     * @var array|null
     */
    private $tableNames;

    /**
     * @var array|null
     */
    private $foreignKeys;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->schema = $connection->getDatabase();
    }

    /**
     * @inheritdoc
     */
    public function getTableNames(): array
    {
        if ($this->tableNames !== null) {
            return $this->tableNames;
        }

        $query = 'SELECT TABLE_NAME '
            . 'FROM INFORMATION_SCHEMA.TABLES '
            . 'WHERE TABLE_TYPE=\'BASE TABLE\' AND TABLE_SCHEMA=? '
            . 'ORDER BY TABLE_NAME ASC';

        $statement = $this->connection->prepare($query);
        $statement->execute([$this->schema]);
        $this->tableNames = $statement->fetchFirstColumn();

        return $this->tableNames;
    }

    /**
     * @inheritdoc
     */
    public function getForeignKeys(): array
    {
        if ($this->foreignKeys !== null) {
            return $this->foreignKeys;
        }

        $query = 'SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME '
            . 'FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE '
            . 'WHERE TABLE_NAME IS NOT NULL AND REFERENCED_TABLE_NAME IS NOT NULL '
            . 'AND TABLE_SCHEMA=? AND CONSTRAINT_SCHEMA=? '
            . 'ORDER BY TABLE_NAME ASC, COLUMN_NAME ASC';

        $statement = $this->connection->prepare($query);
        $statement->execute([$this->schema, $this->schema]);

        $fksByTable = [];

        // Prepare an array that groups foreign key data by constraint name
        while ($row = $statement->fetchAssociative()) {
            $constraintName = $row['CONSTRAINT_NAME'];
            $tableName = $row['TABLE_NAME'];

            if (!isset($fksByTable[$tableName][$constraintName])) {
                $fksByTable[$tableName][$constraintName] = [
                    'constraint_name' => $constraintName,
                    'local_table_name' => $tableName,
                    'local_columns' => [],
                    'foreign_table_name' => $row['REFERENCED_TABLE_NAME'],
                    'foreign_columns' => [],
                ];
            }

            $fksByTable[$tableName][$constraintName]['local_columns'][] = $row['COLUMN_NAME'];
            $fksByTable[$tableName][$constraintName]['foreign_columns'][] = $row['REFERENCED_COLUMN_NAME'];
        }

        $this->foreignKeys = [];

        // Create the foreign keys
        foreach ($fksByTable as $tableName => $fksData) {
            foreach ($fksData as $fkData) {
                $this->foreignKeys[$tableName][] = new ForeignKey(
                    $fkData['constraint_name'],
                    $fkData['local_table_name'],
                    $fkData['local_columns'],
                    $fkData['foreign_table_name'],
                    $fkData['foreign_columns']
                );
            }
        }

        return $this->foreignKeys;
    }

    /**
     * @inheritdoc
     */
    public function getTableForeignKeys(string $tableName): array
    {
        return $this->getForeignKeys()[$tableName] ?? [];
    }
}
