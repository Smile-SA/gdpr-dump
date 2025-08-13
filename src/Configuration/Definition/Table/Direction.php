<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Definition\Table;

enum Direction: string
{
    case ASC = 'ASC';
    case DESC = 'DESC';

    /**
     * Get the direction as a string value.
     */
    public function toString(): string
    {
        return $this->value;
    }
}
