<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Dumper\Sql\Config\Table;

use Smile\Anonymizer\Dumper\Sql\Config\Table\Filter\Filter;
use Smile\Anonymizer\Dumper\Sql\Config\Table\Filter\SortOrder;

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
     * Prepare the table config.
     *
     * @param array $tableData
     */
    private function prepareConfig(array $tableData)
    {
        $this->prepareFilters($tableData);
        $this->prepareLimit($tableData);
        $this->prepareSortOrder($tableData);
        $this->prepareConverters($tableData);
    }

    /**
     * Prepare the table filters.
     *
     * @param array $tableData
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
     * Prepare the table limit.
     *
     * @param array $tableData
     */
    private function prepareLimit(array $tableData)
    {
        if (isset($tableData['limit']) && $tableData['limit']) {
            $this->limit = (int) $tableData['limit'];
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
            $orders = explode(' ', (string) $tableData['orderBy']);
            array_map('trim', $orders);

            foreach ($orders as $order) {
                $parts = explode(' ', $order);
                $column = $parts[0];
                $direction = $parts[1] ?? SortOrder::DIRECTION_ASC;

                $this->sortOrders[] = new SortOrder($column, $direction);
            }
        }
    }

    /**
     * Prepare the table converters.
     *
     * @param array $tableData
     */
    private function prepareConverters(array $tableData)
    {
        if (!array_key_exists('converters', $tableData)) {
            return;
        }

        foreach ($tableData['converters'] as $column => $converterData) {
            // In the config, converters can be an object or a string, or null
            // Cast it to array in order to simplify the next condition
            if (!is_array($converterData)) {
                $converterData = ['converter' => $converterData];
            }

            // Allows to "cancel" a converter definition
            if ($converterData['converter'] === null || $converterData['converter'] === '') {
                continue;
            }

            // Don't validate the converter data, it will be validated by the factory during the object creation
            $this->converters[$column] = $converterData;
        }
    }
}
