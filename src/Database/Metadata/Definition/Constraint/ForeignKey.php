<?php

declare(strict_types=1);

namespace Smile\GdprDump\Database\Metadata\Definition\Constraint;

class ForeignKey
{
    public const ACTION_CASCADE = 'CASCADE';
    public const ACTION_NO_ACTION = 'NO ACTION';
    public const ACTION_RESTRICT = 'RESTRICT';
    public const ACTION_SET_DEFAULT = 'SET_DEFAULT';
    public const ACTION_SET_NULL = 'SET NULL';

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
     * @var string
     */
    private $onUpdate;

    /**
     * @var string
     */
    private $onDelete;

    /**
     * @param string $constraintName
     * @param string $localTableName
     * @param string[] $localColumns
     * @param string $foreignTableName
     * @param string[] $foreignColumns
     * @param string $onUpdate
     * @param string $onDelete
     */
    public function __construct(
        string $constraintName,
        string $localTableName,
        array $localColumns,
        string $foreignTableName,
        array $foreignColumns,
        string $onUpdate = self::ACTION_RESTRICT,
        string $onDelete = self::ACTION_RESTRICT
    ) {
        $this->constraintName = $constraintName;
        $this->localTableName = $localTableName;
        $this->localColumns = $localColumns;
        $this->foreignTableName = $foreignTableName;
        $this->foreignColumns = $foreignColumns;
        $this->onUpdate = $onUpdate;
        $this->onDelete = $onDelete;
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

    /**
     * Get the update action.
     *
     * @return string
     */
    public function getOnUpdate(): string
    {
        return $this->onUpdate;
    }

    /**
     * Get the delete action.
     *
     * @return string
     */
    public function getOnDelete(): string
    {
        return $this->onDelete;
    }
}
