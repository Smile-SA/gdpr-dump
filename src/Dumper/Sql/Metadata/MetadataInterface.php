<?php
declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Sql\Metadata;

use Smile\GdprDump\Dumper\Sql\Metadata\Definition\Constraint\ForeignKey;

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
