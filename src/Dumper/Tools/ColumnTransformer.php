<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Tools;

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
     * @var array|null
     */
    private $currentRow;

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
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function transform(string $table, string $column, $value, array $row)
    {
        // Please keep in mind that this method must be as fast as possible
        // Every micro-optimization counts, this method can be executed millions of times
        // In this part of the code, abstraction layers should be avoided at all costs

        if (!isset($this->converters[$table][$column]) || $value === null) {
            return $value;
        }

        // Set the context data
        if ($this->currentRow !== $row) {
            $this->context['row_data'] = $row;
            $this->context['processed_data'] = [];
            $this->currentRow = $row;
        }

        // Transform the value
        $value = $this->converters[$table][$column]->convert($value, $this->context);
        $this->context['processed_data'][$column] = $value;

        return $value;
    }
}
