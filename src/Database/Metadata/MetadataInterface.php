<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database\Metadata;

use RuntimeException;
use Smile\GdprDump\Database\Metadata\Definition\Constraint\ForeignKey;

interface MetadataInterface
{
    /**
     * Get all table names.
     *
     * @return string[]
     */
    public function getTableNames(): array;

    /**
     * Get the columns of the specified table.
     *
     * @throws RuntimeException
     */
    public function getColumnNames(string $tableName): array;

    /**
     * Get all foreign keys.
     * Each array element is an array that contains the foreign keys of a table.
     */
    public function getForeignKeys(): array;

    /**
     * Get the foreign keys of a table.
     *
     * @return ForeignKey[]
     */
    public function getTableForeignKeys(string $tableName): array;
}
