<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database\Metadata\Definition\Constraint;

class ForeignKey
{
    /**
     * @var string
     */
    private $constraintName;

    /**
     * @var string
     */
    private $localTableName;

    /**
     * @var string[]
     */
    private $localColumns;

    /**
     * @var string
     */
    private $foreignTableName;

    /**
     * @var string[]
     */
    private $foreignColumns;

    /**
     * @param string $constraintName
     * @param string $localTableName
     * @param string[] $localColumns
     * @param string $foreignTableName
     * @param string[] $foreignColumns
     */
    public function __construct(
        string $constraintName,
        string $localTableName,
        array $localColumns,
        string $foreignTableName,
        array $foreignColumns
    ) {
        $this->constraintName = $constraintName;
        $this->localTableName = $localTableName;
        $this->localColumns = $localColumns;
        $this->foreignTableName = $foreignTableName;
        $this->foreignColumns = $foreignColumns;
    }

    /**
     * Get the name of the constraint.
     *
     * @return string
     */
    public function getConstraintName(): string
    {
        return $this->constraintName;
    }

    /**
     * Get the name of the local table.
     *
     * @return string
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
     *
     * @return string
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
