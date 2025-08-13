<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Definition\Table;

final class SortOrder
{
    public function __construct(private string $column, private Direction $direction = Direction::ASC)
    {
    }

    /**
     * Get the column name.
     */
    public function getColumn(): string
    {
        return $this->column;
    }

    /**
     * Get the sort direction.
     */
    public function getDirection(): Direction
    {
        return $this->direction;
    }
}
