<?php

declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Config\Table;

use Smile\GdprDump\Converter\ConditionBuilder;
use Smile\GdprDump\Dumper\Config\Table\Filter\Filter;
use Smile\GdprDump\Dumper\Config\Table\Filter\SortOrder;
use UnexpectedValueException;

class TableConfig
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var Filter[]
     */
    private array $filters = [];

    /**
     * @var SortOrder[]
     */
    private array $sortOrders = [];

    /**
     * @var int|null
     */
    private ?int $limit = null;

    /**
     * @var array
     */
    private array $converters = [];

    /**
     * @var string
     */
    private string $skipCondition = '';

    /**
     * @param string $tableName
     * @param array $tableConfig
     */
    public function __construct(string $tableName, array $tableConfig)
    {
        $this->name = $tableName;
        $this->prepareConfig($tableConfig);
    }

    /**
     * Get the table name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the filters.
     *
     * @return Filter[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Get the sort orders.
     *
     * @return SortOrder[]
     */
    public function getSortOrders(): array
    {
        return $this->sortOrders;
    }

    /**
     * Get the table limit.
     *
     * @return int|null
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * Get the converter definitions of a table.
     *
     * @return array
     */
    public function getConverters(): array
    {
        return $this->converters;
    }

    /**
     * Check if there is data to filter.
     *
     * @return bool
     */
    public function hasFilter(): bool
    {
        return !empty($this->filters);
    }

    /**
     * Check if the table data must be sorted.
     *
     * @return bool
     */
    public function hasSortOrder(): bool
    {
        return !empty($this->sortOrders);
    }

    /**
     * Check if a limit is defined.
     *
     * @return bool
     */
    public function hasLimit(): bool
    {
        return $this->limit !== null;
    }

    /**
     * Get the conversion skip condition.
     * Data conversion is disabled when the condition evaluates to true.
     *
     * @return string
     */
    public function getSkipCondition(): string
    {
        return $this->skipCondition;
    }

    /**
     * Prepare the table config.
     *
     * @param array $tableData
     */
    private function prepareConfig(array $tableData): void
    {
        $this->prepareFilters($tableData);
        $this->prepareSortOrder($tableData);
        $this->prepareLimit($tableData);
        $this->prepareConverters($tableData);
    }

    /**
     * Prepare the table filters.
     *
     * @param array $tableData
     */
    private function prepareFilters(array $tableData): void
    {
        if (isset($tableData['filters'])) {
            foreach ($tableData['filters'] as $filter) {
                $this->filters[] = new Filter((string) $filter[0], (string) $filter[1], $filter[2] ?? null);
            }
        }
    }

    /**
     * Prepare the table sort order.
     *
     * @param array $tableData
     */
    private function prepareSortOrder(array $tableData): void
    {
        $orderBy = (string) ($tableData['order_by'] ?? '');
        if ($orderBy === '') {
            return;
        }

        $orders = explode(',', $orderBy);
        $orders = array_map('trim', $orders);

        foreach ($orders as $order) {
            $parts = explode(' ', $order);

            if (count($parts) > 2) {
                throw new UnexpectedValueException(sprintf('The sort order "%s" is not valid.', $order));
            }

            $column = $parts[0];
            $direction = $parts[1] ?? SortOrder::DIRECTION_ASC;

            $this->sortOrders[] = new SortOrder($column, $direction);
        }
    }

    /**
     * Prepare the table limit.
     *
     * @param array $tableData
     */
    private function prepareLimit(array $tableData): void
    {
        if (isset($tableData['limit']) && $tableData['limit'] > 0) {
            $this->limit = (int) $tableData['limit'];
        }

        if (isset($tableData['truncate']) && $tableData['truncate']) {
            $this->limit = 0;
        }
    }

    /**
     * Prepare the table converters.
     *
     * @param array $tableData
     */
    private function prepareConverters(array $tableData): void
    {
        if (isset($tableData['converters'])) {
            foreach ($tableData['converters'] as $column => $converterData) {
                // Ignore disabled converters
                if (array_key_exists('disabled', $converterData) && $converterData['disabled']) {
                    break;
                }

                // Converter data will be validated by the factory during the object creation
                $this->converters[$column] = $converterData;
            }
        }

        $skipCondition = (string) ($tableData['skip_conversion_if'] ?? '');
        if ($skipCondition !== '') {
            $conditionBuilder = new ConditionBuilder();
            $this->skipCondition = $conditionBuilder->build($skipCondition);
        }
    }
}
