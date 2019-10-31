<?php
declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Sql\Config\Table;

use Smile\GdprDump\Dumper\Sql\Config\Table\Filter\Filter;
use Smile\GdprDump\Dumper\Sql\Config\Table\Filter\SortOrder;
use UnexpectedValueException;

class TableConfig
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Filter[]
     */
    private $filters = [];

    /**
     * @var SortOrder[]
     */
    private $sortOrders = [];

    /**
     * @var int|null
     */
    private $limit;

    /**
     * @var array
     */
    private $converters = [];

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
    public function getLimit()
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
     * Prepare the table config.
     *
     * @param array $tableData
     */
    private function prepareConfig(array $tableData)
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
     * @throws UnexpectedValueException
     */
    private function prepareFilters(array $tableData)
    {
        if (isset($tableData['filters'])) {
            foreach ($tableData['filters'] as $filter) {
                $this->filters[] = new Filter($filter[0], $filter[1], $filter[2] ?? null);
            }
        }
    }

    /**
     * Prepare the table sort order.
     *
     * @param array $tableData
     */
    private function prepareSortOrder(array $tableData)
    {
        if (isset($tableData['orderBy']) && $tableData['orderBy']) {
            $orders = explode(',', (string) $tableData['orderBy']);
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
    }

    /**
     * Prepare the table limit.
     *
     * @param array $tableData
     */
    private function prepareLimit(array $tableData)
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
    private function prepareConverters(array $tableData)
    {
        if (!isset($tableData['converters'])) {
            return;
        }

        foreach ($tableData['converters'] as $column => $converterData) {
            // Converter data will be validated by the factory during the object creation
            $this->converters[$column] = $converterData;
        }
    }
}
