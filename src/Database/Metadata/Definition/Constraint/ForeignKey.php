<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database\Metadata\Definition\Constraint;

class ForeignKey
{
    /**
     * @param string[] $localColumns
     * @param string[] $foreignColumns
     */
    public function __construct(
        private string $constraintName,
        private string $localTableName,
        private array $localColumns,
        private string $foreignTableName,
        private array $foreignColumns
    ) {
    }

    /**
     * Get the name of the constraint.
     */
    public function getConstraintName(): string
    {
        return $this->constraintName;
    }

    /**
     * Get the name of the local table.
     */
    public function getLocalTableName(): string
    {
        return $this->localTableName;
    }

    /**
     * Get the name of the local columns.
     *
     * @return string[]
     */
    public function getLocalColumns(): array
    {
        return $this->localColumns;
    }

    /**
     * Get the name of the foreign table.
     */
    public function getForeignTableName(): string
    {
        return $this->foreignTableName;
    }

    /**
     * Get the name of the foreign columns.
     *
     * @return string[]
     */
    public function getForeignColumns(): array
    {
        return $this->foreignColumns;
    }
}
