<?php
declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Sql\Metadata;

use Doctrine\DBAL\Connection;
use PDO;
use Smile\GdprDump\Dumper\Sql\Metadata\Definition\Constraint\ForeignKey;

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
        $query = 'SELECT TABLE_NAME '
            . 'FROM INFORMATION_SCHEMA.TABLES '
            . 'WHERE TABLE_TYPE="BASE TABLE" AND TABLE_SCHEMA=? '
            . 'ORDER BY TABLE_NAME ASC';

        $statement = $this->connection->prepare($query);
        $statement->execute([$this->schema]);

        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @inheritdoc
     */
    public function getForeignKeys(string $tableName): array
    {
        $query = 'SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, '
            . 'REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME '
            . 'FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE '
            . 'WHERE REFERENCED_TABLE_NAME IS NOT NULL AND TABLE_NAME=? AND TABLE_SCHEMA=? AND CONSTRAINT_SCHEMA=? '
            . 'ORDER BY TABLE_NAME ASC';

        $statement = $this->connection->prepare($query);
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        $statement->execute([$tableName, $this->schema, $this->schema]);

        $fksData = [];

        // Prepare an array that groups foreign key data by constraint name
        while ($row = $statement->fetch()) {
            $constraintName = $row['CONSTRAINT_NAME'];

            if (!isset($fksData[$constraintName])) {
                $fksData[$constraintName] = [
                    'constraint_name' => $constraintName,
                    'local_table_name' => $row['TABLE_NAME'],
                    'local_columns' => [],
                    'foreign_table_name' => $row['REFERENCED_TABLE_NAME'],
                    'foreign_columns' => [],
                ];
            }

            $fksData[$constraintName]['local_columns'][] = $row['COLUMN_NAME'];
            $fksData[$constraintName]['foreign_columns'][] = $row['REFERENCED_COLUMN_NAME'];
        }

        $foreignKeys = [];

        // Create the foreign keys
        foreach ($fksData as $fkData) {
            $foreignKeys[] = new ForeignKey(
                $fkData['constraint_name'],
                $fkData['local_table_name'],
                $fkData['local_columns'],
                $fkData['foreign_table_name'],
                $fkData['foreign_columns']
            );
        }

        return $foreignKeys;
    }
}
