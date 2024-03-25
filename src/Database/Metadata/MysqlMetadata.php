<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database\Metadata;

use Doctrine\DBAL\Connection;
use RuntimeException;
use Smile\GdprDump\Database\Metadata\Definition\Constraint\ForeignKey;

class MysqlMetadata implements MetadataInterface
{
    private string $schema;
    private ?array $tableNames = null;
    private ?array $columnNames = null;
    private ?array $foreignKeys = null;

    public function __construct(private Connection $connection)
    {
        $this->schema = (string) $connection->getDatabase();
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
        $this->tableNames = $statement->executeQuery([$this->schema])->fetchFirstColumn();

        return $this->tableNames;
    }

    /**
     * @inheritdoc
     */
    public function getColumnNames(string $tableName): array
    {
        if ($this->columnNames === null) {
            $query = 'SELECT TABLE_NAME, COLUMN_NAME '
                . 'FROM INFORMATION_SCHEMA.COLUMNS '
                . 'WHERE TABLE_SCHEMA=?'
                . 'ORDER BY COLUMN_NAME ASC';

            $statement = $this->connection->prepare($query);
            $result = $statement->executeQuery([$this->schema]);

            $this->columnNames = [];

            while ($row = $result->fetchAssociative()) {
                $this->columnNames[$row['TABLE_NAME']][] = $row['COLUMN_NAME'];
            }
        }

        return $this->columnNames[$tableName]
            ?? throw new RuntimeException(sprintf('The table "%s" is not defined.', $tableName));
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
        $result = $statement->executeQuery([$this->schema, $this->schema]);

        $fksByTable = [];

        // Prepare an array that groups foreign key data by constraint name
        while ($row = $result->fetchAssociative()) {
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
