<?php
declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Sql;

use Smile\GdprDump\Converter\ConverterInterface;

class ColumnTransformer
{
    /**
     * @var ConverterInterface[]
     */
    private $converters = [];

    /**
     * @var array
     */
    private $context = [];

    /**
     * @param ConverterInterface[] $converters
     * @param array $context
     */
    public function __construct(array $converters, array $context = [])
    {
        $this->converters = $converters;
        $this->context = $context;
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
            $this->context['row_data'] = $row;
            $value = $this->converters[$table][$column]->convert($value, $this->context);
        }

        return $value;
    }
}
