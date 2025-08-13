<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Definition\Table;

use Smile\GdprDump\Configuration\Exception\InvalidConfigException;

final class SortOrder
{
    public const DIRECTION_ASC = 'ASC';
    public const DIRECTION_DESC = 'DESC';

    private string $direction;

    /**
     * @throws InvalidConfigException
     */
    public function __construct(private string $column, string $direction = self::DIRECTION_ASC)
    {
        $direction = strtoupper($direction);

        if ($direction !== self::DIRECTION_ASC && $direction !== self::DIRECTION_DESC) {
            throw new InvalidConfigException(sprintf('Invalid sort direction "%s".', $direction));
        }

        $this->direction = $direction;
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
    public function getDirection(): string
    {
        return $this->direction;
    }
}
