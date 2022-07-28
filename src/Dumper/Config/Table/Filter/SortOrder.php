<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Config\Table\Filter;

use UnexpectedValueException;

class SortOrder
{
    /**
     * Ascending direction.
     */
    public const DIRECTION_ASC = 'ASC';

    /**
     * Descending direction.
     */
    public const DIRECTION_DESC = 'DESC';

    /**
     * @var string
     */
    private string $column;

    /**
     * @var string
     */
    private string $direction;

    /**
     * @param string $column
     * @param string $direction
     */
    public function __construct(string $column, string $direction = self::DIRECTION_ASC)
    {
        $direction = strtoupper($direction);

        if ($direction !== self::DIRECTION_ASC && $direction !== self::DIRECTION_DESC) {
            throw new UnexpectedValueException(sprintf('Invalid sort direction "%s".', $direction));
        }

        $this->column = $column;
        $this->direction = $direction;
    }

    /**
     * Get the column name.
     *
     * @return string
     */
    public function getColumn(): string
    {
        return $this->column;
    }

    /**
     * Get the sort direction.
     *
     * @return string
     */
    public function getDirection(): string
    {
        return $this->direction;
    }
}
