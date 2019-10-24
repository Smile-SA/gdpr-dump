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
        $query = 'SELECT CONSTRAINT_NAME, TABLE_NAME, GROUP_CONCAT(COLUMN_NAME) AS COLUMN_NAMES, '
            . 'REFERENCED_TABLE_NAME, GROUP_CONCAT(REFERENCED_COLUMN_NAME) AS REFERENCED_COLUMN_NAMES '
            . 'FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE '
            . 'WHERE REFERENCED_TABLE_NAME IS NOT NULL AND TABLE_NAME=? AND TABLE_SCHEMA=? AND CONSTRAINT_SCHEMA=?'
            . 'GROUP BY CONSTRAINT_NAME '
            . 'ORDER BY TABLE_NAME ASC';

        $statement = $this->connection->prepare($query);
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        $statement->execute([$tableName, $this->schema, $this->schema]);

        $foreignKeys = [];

        while ($row = $statement->fetch()) {
            $foreignKeys[] = new ForeignKey(
                $row['CONSTRAINT_NAME'],
                $row['TABLE_NAME'],
                explode(',', $row['COLUMN_NAMES']),
                $row['REFERENCED_TABLE_NAME'],
                explode(',', $row['REFERENCED_COLUMN_NAMES'])
            );
        }

        return $foreignKeys;
    }
}
