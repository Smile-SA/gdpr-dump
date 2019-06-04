<?php
declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Sql;

class ColumnTransformer
{
    /**
     * @var array
     */
    private $converters = [];

    /**
     * @param array $converters
     */
    public function __construct(array $converters = [])
    {
        $this->converters = $converters;
    }

    /**
     * Transform a column value.
     *
     * @param string $table
     * @param string $column
     * @param mixed $value
     * @param array $row
     * @return mixed
     */
    public function transform(string $table, string $column, $value, array $row)
    {
        // Please keep in mind that this method must be as fast as possible
        // Every micro-optimization counts, this method can be executed millions of times
        // In this part of the code, abstraction layers should be avoided at all costs

        if ($value === null) {
            return $value;
        }

        if (isset($this->converters[$table][$column])) {
            $value = $this->converters[$table][$column]->convert($value, $row);
        }

        return $value;
    }
}
