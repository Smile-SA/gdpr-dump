<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Dumper\Sql\Config\Table\Filter;

class SortOrder
{
    /**
     * Ascending direction.
     */
    const DIRECTION_ASC = 'ASC';

    /**
     * Descending direction.
     */
    const DIRECTION_DESC = 'DESC';

    /**
     * @var string
     */
    private $column;

    /**
     * @var string
     */
    private $direction;

    /**
     * @param string $column
     * @param string $direction
     */
    public function __construct(string $column, string $direction = self::DIRECTION_ASC)
    {
        $direction = strtoupper($direction);

        if ($direction !== self::DIRECTION_ASC && $direction !== self::DIRECTION_DESC) {
            throw new \UnexpectedValueException(sprintf('Invalid sort direction "%s".', $direction));
        }

        $this->column = $column;
        $this->direction = $direction;
    }

    /**
     * Get the column name.
     *
     * @return string
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * Get the sort direction.
     *
     * @return string
     */
    public function getDirection()
    {
        return $this->direction;
    }
}
