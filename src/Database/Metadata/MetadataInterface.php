<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database\Metadata;

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
     * Get the foreign keys of a table.
     *
     * @param string $tableName
     * @return ForeignKey[]
     */
    public function getForeignKeys(string $tableName): array;
}
