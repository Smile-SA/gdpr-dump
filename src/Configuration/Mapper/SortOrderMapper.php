<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Mapper;

use Smile\GdprDump\Configuration\Definition\Table\Direction;
use Smile\GdprDump\Configuration\Definition\Table\SortOrder;
use Smile\GdprDump\Configuration\Exception\UnexpectedValueException;

final class SortOrderMapper
{
    /**
     * Build an array of SortOrder objects from the provided string (e.g. "name desc, id")
     *
     * @return SortOrder[]
     */
    public function fromString(string $orderBy): array
    {
        if ($orderBy === '') {
            return [];
        }

        $orders = explode(',', $orderBy);
        $orders = array_map('trim', $orders);

        return array_map(fn (string $order) => $this->createSortOrder($order), $orders);
    }

    /**
     * Create a sort order object from a string, e.g. "id desc"
     */
    private function createSortOrder(string $input): SortOrder
    {
        $parts = explode(' ', $input);
        if (count($parts) > 2) {
            throw new UnexpectedValueException(sprintf('The sort order "%s" is not valid.', $input));
        }

        $column = $parts[0];
        if ($column === '') {
            throw new UnexpectedValueException(sprintf('The sort order "%s" is not valid.', $input));
        }

        $direction = match (strtoupper($parts[1] ?? 'ASC')) {
            'ASC' => Direction::ASC,
            'DESC' => Direction::DESC,
            default => throw new UnexpectedValueException(sprintf('Invalid sort direction "%s".', $parts[1])),
        };

        return new SortOrder($column, $direction);
    }
}
