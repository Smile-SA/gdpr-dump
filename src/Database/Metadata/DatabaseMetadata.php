<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database\Metadata;

use Doctrine\DBAL\Exception as DBALException;
use Smile\GdprDump\Database\Metadata\Definition\ForeignKey;

interface DatabaseMetadata
{
    /**
     * Get all table names.
     *
     * @return string[]
     * @throws DBALException
     */
    public function getTableNames(): array;

    /**
     * Get the columns of the specified table.
     *
     * @throws DBALException
     */
    public function getColumnNames(string $tableName): array;

    /**
     * Get all foreign keys.
     * Each array element is an array that contains the foreign keys of a table.
     *
     * @throws DBALException
     */
    public function getForeignKeys(): array;

    /**
     * Get the foreign keys of a table.
     *
     * @return ForeignKey[]
     * @throws DBALException
     */
    public function getTableForeignKeys(string $tableName): array;
}
